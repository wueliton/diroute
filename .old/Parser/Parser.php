<?php

namespace Example\Parser;

use Example\AST\AttributeNode;
use Example\AST\ComponentNode;
use Example\AST\ExpressionNode;
use Example\AST\GenericDirectiveNode;
use Example\AST\InterpolatedValueNode;
use Example\AST\TextNode;
use Example\Directive\DirectiveConfig;
use Example\Directive\DirectiveRegistry;
use Example\Parser\TokenType;
use Example\Parser\TokenStream;

class Parser
{
    public function parser(TokenStream $stream, ?TokenType $stop = null): array
    {
        $nodes = [];

        while (!$stream->isAtEnd() && ($stop === null || !$stream->match($stop))) {
            $node = $this->parseNode($stream);
            if ($node === null) {
                continue;
            }

            $lastIndex = count($nodes) - 1;
            if ($lastIndex >= 0 && $node instanceof TextNode && $nodes[$lastIndex] instanceof TextNode) {
                $nodes[$lastIndex] = new TextNode($nodes[$lastIndex]->text . $node->text);
                continue;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    private function parseNode(TokenStream $stream)
    {
        if ($this->isDirectiveToken($stream)) {
            return $this->parseDirective($stream);
        }

        if ($stream->match(TokenType::EXPRESSION)) {
            return $this->parseExpression($stream);
        }

        if ($stream->match(TokenType::COMPONENT_START)) {
            return $this->parseComponent($stream);
        }

        return $this->parseTextOrHtml($stream);
    }

    private function isDirectiveToken(TokenStream $stream): bool
    {
        if (!$stream->match(TokenType::DIRECTIVE_NAME)) {
            return false;
        }

        $token = $stream->peek();
        return DirectiveRegistry::isRegistered($token->text);
    }

    private function parseDirective(TokenStream $stream): ?GenericDirectiveNode
    {
        $token = $stream->consume();
        $directive = DirectiveRegistry::get($token->text);

        if (!$directive) {
            return null;
        }

        $arguments = $this->extractDirectiveArguments($stream);
        $children = $this->parseDirectiveScope($stream);
        $currentNode = new GenericDirectiveNode($directive->name, $arguments, $children);

        if (!empty($directive->allowedConnections)) {
            $this->attachConnectedDirectives($currentNode, $directive, $stream);
        }

        return $currentNode;
    }

    private function extractDirectiveArguments(TokenStream $stream): ?string
    {
        if ($stream->match(TokenType::ARGUMENTS)) {
            return $stream->consume()->text;
        }
        return null;
    }

    private function parseDirectiveScope(TokenStream $stream): array
    {
        if (!$stream->match(TokenType::SCOPE_START)) {
            return [];
        }

        $stream->consume();
        $children = $this->parser($stream, TokenType::SCOPE_END);
        $stream->consume();

        return $children;
    }

    private function attachConnectedDirectives(
        GenericDirectiveNode $currentNode,
        ?DirectiveConfig $definition,
        TokenStream $stream
    ): void {
        while (true) {
            $this->skipWhitespacesTokens($stream);

            if (!$this->hasValidConnectedDirective($stream, $definition)) {
                break;
            }

            $connectedNode = $this->parseDirective($stream);
            if ($connectedNode) {
                $currentNode->addConnection($connectedNode);
                $definition = DirectiveRegistry::get($connectedNode->name);
            }
        }
    }

    private function hasValidConnectedDirective(TokenStream $stream, ?DirectiveConfig $definition): bool
    {
        if (!$stream->match(TokenType::DIRECTIVE_NAME)) {
            return false;
        }

        $nextDirectiveName = $stream->peek()->text;
        return in_array($nextDirectiveName, $definition->allowedConnections);
    }

    private function parseExpression(TokenStream $stream): ExpressionNode
    {
        $token = $stream->consume();
        return new ExpressionNode($token->text);
    }

    private function parseTextOrHtml(TokenStream $stream): TextNode
    {
        $token = $stream->consume();
        return new TextNode($token->text);
    }

    private function parseComponent(TokenStream $stream): ComponentNode
    {
        $token = $stream->consume();
        $nextToken = $stream->peek();
        $isAttributes = $nextToken->type === TokenType::ATTRIBUTES;
        $attributes = [];

        if ($isAttributes) {
            $attributesNode = $stream->consume();
            $attributes = $this->compileAttributes($attributesNode);
        }

        $children = $this->parser($stream, TokenType::COMPONENT_END);
        $stream->consume();
        return new ComponentNode($token->text, $attributes, $children);
    }

    private function skipWhitespacesTokens(TokenStream $stream): void
    {
        while ($stream->match(TokenType::TEXT) && trim($stream->peek()->text) === '') {
            $stream->consume();
        }
    }

    private function compileAttributes(Token $token)
    {
        $attributes = [];
        $rawAttributes = $token->text;
        $maxLength = strlen($rawAttributes);
        $cursor = 0;
        $nameDelimiters = "=";

        while ($cursor < $maxLength) {
            $emptyChar = $rawAttributes[$cursor] === ' ' || htmlspecialchars_decode($rawAttributes[$cursor]) === "\t" || $rawAttributes[$cursor] === "\n" || $rawAttributes[$cursor] === "\r";
            if ($emptyChar) {
                $cursor++;
                continue;
            }

            $nameLength = strcspn($rawAttributes, $nameDelimiters, $cursor);
            $attrName = substr($rawAttributes, $cursor, $nameLength);
            $cursor += $nameLength;
            $hasPropValue = $cursor < $maxLength && $rawAttributes[$cursor] === '=';

            if ($hasPropValue) {
                $cursor++;
                $firstChar = $rawAttributes[$cursor];
                $isQuote = $firstChar === "'" || $firstChar === '"';
                $cursor++;

                if ($isQuote) {
                    $valueLength = strcspn($rawAttributes, $firstChar, $cursor);
                    $value = substr($rawAttributes, $cursor, $valueLength);
                    $cursor += $valueLength + 1;
                    $attributes[] = new AttributeNode($attrName, $this->compileAttributeValue($value));
                }
            } else {
                $attributes[] = new AttributeNode($attrName, true);
            }
        }

        return $attributes;
    }

    private function compileAttributeValue(string $rawValue)
    {
        if (!str_contains($rawValue, '{{')) {
            return new TextNode($rawValue);
        }

        $parts = preg_split('/(\{\{.*?\}\})/', $rawValue, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $astParts = [];

        foreach ($parts as $part) {
            if (str_starts_with($part, '{{') && str_ends_with($part, '}}')) {
                $expr = trim(substr($part, 2, -2));
                $astParts[] = new ExpressionNode($expr);
            } else {
                $astParts[] = new TextNode($part);
            }
        }

        return new InterpolatedValueNode($astParts);
    }
}
