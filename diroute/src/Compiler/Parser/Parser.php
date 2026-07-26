<?php

namespace Diroute\Compiler\Parser;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Lexer\Token;
use Diroute\Compiler\Lexer\TokenType;
use Diroute\Compiler\Parser\Node\AttributeNode;
use Diroute\Compiler\Parser\Node\ComponentNode;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\RootNode;
use Diroute\Compiler\Parser\Node\TextNode;
use Diroute\Compiler\Parser\Registry\DirectiveRegistry;
use RuntimeException;

class Parser
{
    private int $cursor = 0;

    /** @var Token[] */
    private array $tokens = [];

    public function __construct(
        private readonly DirectiveRegistry $registry
    ) {}

    public function parse(array $tokens): AST
    {
        $this->tokens = $tokens;
        $this->cursor = 0;
        $root = new RootNode();

        while (!$this->isEOF()) {
            $node = $this->parseNode();

            // Descarta nós de texto contendo apenas espaços em branco no nível raiz
            if ($node instanceof TextNode && $node->isRawHtml && \trim($node->content) === '') {
                continue;
            }

            if ($node !== null) {
                $root->addChild($node);
            }
        }

        return new AST($root);
    }

    private function parseNode(): ?NodeInterface
    {
        if ($this->isEOF()) {
            return null;
        }

        $token = $this->currentToken();

        if ($token->type === TokenType::T_HTML) {
            $this->advance();
            return $this->parseTextNode($token, isRawHtml: true);
        }

        if ($token->type === TokenType::T_INTERPOLATION) {
            $this->advance();
            return $this->parseTextNode($token, isRawHtml: false);
        }

        if ($token->type === TokenType::T_DIRECTIVE_NAME) {
            if ($this->registry->has($token->value)) {
                return $this->parseDirective();
            }

            // Caso seja uma diretiva não registrada (ex: @twitter), faz fallback para texto plano
            $this->advance();
            return $this->parseTextNode($token, isRawHtml: true, prefix: '@');
        }

        if ($token->type === TokenType::T_COMPONENT_OPEN) {
            return $this->parseComponent($token);
        }

        // Avança o cursor caso encontre tokens estruturais soltos ({ ou })
        $this->advance();
        return null;
    }

    private function parseComponent(Token $token): ComponentNode
    {
        $selector = $token->value;

        $this->advance();

        $props = [];

        if (!$this->isEOF() && $this->currentToken()->type === TokenType::T_COMPONENT_PROPS) {
            $props = $this->parseComponentProps();
            $this->advance();
        }

        $children = [];
        if (!$this->isEOF() && $this->currentToken()->type !== TokenType::T_COMPONENT_CLOSE) {
            $this->advance();
            $children = $this->parseComponentChildren();
        }

        return new ComponentNode($selector, $props, $children, $token->line, $token->column);
    }

    private function parseComponentProps(): array
    {
        $token = $this->currentToken();
        $rawValue = trim($token->value);

        if (empty($rawValue)) {
            return [];
        }

        $attributes = [];
        $length = strlen($rawValue);
        $cursor = 0;

        while ($cursor < $length) {
            $nameStart = $cursor;

            while ($cursor < $length && $rawValue[$cursor] <= ' ') {
                $cursor++;
            }

            while ($cursor < $length && $rawValue[$cursor] > ' ' && $rawValue[$cursor] !== '=') {
                $cursor++;
            }

            $propName = substr($rawValue, $nameStart, $cursor - $nameStart);

            if ($cursor >= $length) {
                break;
            }

            $cursor++;
            $nextChar = $rawValue[$cursor];
            $propValue = true;

            if ($nextChar === '"' || $nextChar === "'") {
                $quoteProp = $nextChar;
                $cursor++;
                $valueStart = $cursor;

                while ($cursor < $length && $rawValue[$cursor] !== $quoteProp) {
                    $cursor++;
                }

                $propStr = substr($rawValue, $valueStart, $cursor - $valueStart);
                $propValue = $this->getAttributeValue($propStr);
            }

            $isBoolean = empty($propValue);
            $isBinding = (substr($propName, 0, 1) === '[' && substr($propName, 0, -1) == ']') || str_contains($propValue, "{{");

            $attributes[] = new AttributeNode($propName, $propValue, $isBoolean, $isBinding);
            $cursor++;
        }

        return $attributes;
    }

    private function getAttributeValue(string $value)
    {
        if (strpos($value, '{{') === false) {
            return var_export($value, true);
        }

        $length = strlen($value);
        $cursor = 0;

        $parts = [];
        $buffer = '';

        while ($cursor < $length) {
            if (
                $value[$cursor] === '{'
                && $cursor + 1 < $length
                && $value[$cursor + 1] === '{'
            ) {
                if ($buffer !== '') {
                    $parts[] = var_export($buffer, true);
                    $buffer = '';
                }

                $cursor += 2;
                $expression = '';

                while (
                    $cursor < $length
                    && !(
                        $value[$cursor] === '}'
                        && $cursor + 1 < $length
                        && $value[$cursor + 1] === '}'
                    )
                ) {
                    $expression .= $value[$cursor];
                    $cursor++;
                }

                $cursor += 2;
                $parts[] = 'htmlspecialchars(' . trim($expression) . ')';
                continue;
            }

            $buffer .= $value[$cursor];
            $cursor++;
        }

        if ($buffer !== '') {
            $parts[] = var_export($buffer, true);
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return implode(' . ', $parts);
    }

    private function parseComponentChildren(): array
    {
        $children = [];

        while (!$this->isEOF()) {
            $token = $this->currentToken();

            if ($token->type === TokenType::T_COMPONENT_CLOSE) {
                $this->advance();
                break;
            }

            $node = $this->parseNode();
            if ($node !== null) {
                $children[] = $node;
            }
        }

        return $children;
    }

    private function parseTextNode(Token $token, bool $isRawHtml, ?string $prefix = null): TextNode
    {
        return new TextNode(
            content: ($prefix ?? '') . $token->value,
            isRawHtml: $isRawHtml,
            line: $token->line,
            column: $token->column
        );
    }

    private function parseDirective(bool $isBranch = false): DirectiveNode
    {
        $token = $this->currentToken();
        $directiveName = $token->value;
        $line = $token->line;
        $column = $token->column;

        // Recupera as configurações dinâmicas da diretiva
        $config = $this->registry->get($directiveName);
        $this->advance(); // Consome T_DIRECTIVE_NAME

        $expression = null;

        // 1. Processamento e Validação de Argumentos (...)
        if (!$this->isEOF() && $this->currentToken()->type === TokenType::T_DIRECTIVE_ARG) {
            $expression = $this->currentToken()->value;
            $this->advance();
        } elseif ($config->hasArguments) {
            throw new RuntimeException(
                "A diretiva '@{$directiveName}' exige argumentos entre parênteses. Linha {$line}, coluna {$column}."
            );
        }

        // Pula espaços e quebras de linha antes da abertura do bloco '{'
        $this->skipIgnorableTokens();

        // 2. Processamento do Bloco de Conteúdo { ... }
        $children = [];
        if (!$this->isEOF() && $this->currentToken()->type === TokenType::T_BLOCK_OPEN) {
            $this->advance(); // Consome '{'
            $children = $this->parseBlockChildren($config->allowedConnections);
        }

        $directiveNode = new DirectiveNode(
            name: $directiveName,
            expression: $expression,
            rendererClass: $config->rendererClass,
            children: $children,
            branches: [],
            line: $line,
            column: $column
        );

        // Se o nó atual já for uma ramificação (@elseif, @else, @empty),
        // encerra e devolve para ser encadeado no nó pai principal.
        if ($isBranch) {
            return $directiveNode;
        }

        // 3. Processamento Linear de Ramificações no Nó Pai (@if, @for)
        while (!$this->isEOF()) {
            $this->skipIgnorableTokens();

            if ($this->currentToken()->type !== TokenType::T_DIRECTIVE_NAME) {
                break;
            }

            $candidateName = $this->currentToken()->value;

            if (\in_array($candidateName, $config->allowedConnections, true)) {
                $branch = $this->parseDirective(isBranch: true);
                $directiveNode->addBranch($branch);
            } else {
                break;
            }
        }

        return $directiveNode;
    }

    /**
     * Processa os nós até encontrar '}' ou a próxima diretiva de conexão declarada.
     * 
     * @param string[] $allowedConnections
     * @return NodeInterface[]
     */
    private function parseBlockChildren(array $allowedConnections): array
    {
        $children = [];

        while (!$this->isEOF()) {
            $token = $this->currentToken();

            if ($token->type === TokenType::T_BLOCK_CLOSE) {
                $this->advance(); // Consome '}'
                break;
            }

            if ($token->type === TokenType::T_DIRECTIVE_NAME) {
                $candidateName = $token->value;
                if (\in_array($candidateName, $allowedConnections, true)) {
                    break;
                }
            }

            $node = $this->parseNode();
            if ($node !== null) {
                $children[] = $node;
            }
        }

        return $children;
    }

    /**
     * Pula tokens de texto que contêm exclusivamente espaços em branco/quebras de linha.
     */
    private function skipIgnorableTokens(): void
    {
        while (!$this->isEOF()) {
            $token = $this->currentToken();
            if ($token->type === TokenType::T_HTML && \trim($token->value) === '') {
                $this->advance();
            } else {
                break;
            }
        }
    }

    private function currentToken(): Token
    {
        return $this->tokens[$this->cursor];
    }

    private function advance(): void
    {
        if (!$this->isEOF()) {
            $this->cursor++;
        }
    }

    private function isEOF(): bool
    {
        return $this->cursor >= \count($this->tokens) || $this->tokens[$this->cursor]->type === TokenType::T_EOF;
    }
}
