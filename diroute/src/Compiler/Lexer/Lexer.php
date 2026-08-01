<?php

namespace Diroute\Compiler\Lexer;

use Diroute\Compiler\Lexer\TemplateScanner;
use Diroute\Compiler\Lexer\TokenType;

class Lexer
{
    /** @var Token[] */
    private array $tokens = [];

    public function tokenize(string $source): array
    {
        $scanner = new TemplateScanner($source);
        $this->tokens = [];

        while (!$scanner->isEOF()) {
            if ($this->isComponentClose($scanner)) {
                $this->tokenizeComponentClose($scanner);
                continue;
            }
            if ($this->isComponent($scanner)) {
                $this->tokenizeComponent($scanner);
                continue;
            }
            if ($this->isInterpolation($scanner)) {
                $this->tokenizeInterpolation($scanner);
                continue;
            }
            if ($this->isDirective($scanner)) {
                $this->tokenizeDirective($scanner);
                continue;
            }
            if ($this->isBlockClose($scanner)) {
                $this->tokenizeBlockClose($scanner);
                continue;
            }

            $this->consumeStaticHtml($scanner);
        }

        $this->tokens[] = new Token(TokenType::T_EOF, '', $scanner->getLine(), $scanner->getColumn());

        return $this->tokens;
    }

    private function isInterpolation(TemplateScanner $scanner): bool
    {
        return $scanner->currentChar() === '{' && $scanner->peek(2) === '{{';
    }

    private function isDirective(TemplateScanner $scanner): bool
    {
        return $scanner->currentChar() === '@';
    }

    private function isBlockClose(TemplateScanner $scanner): bool
    {
        return $scanner->currentChar() === '}';
    }

    private function isComponent(TemplateScanner $scanner): bool
    {
        return $scanner->currentChar() === '<';
    }

    private function isComponentClose(TemplateScanner $scanner): bool
    {
        return $scanner->peek(2) === '</';
    }

    private function tokenizeComponentClose(TemplateScanner $scanner)
    {
        $line = $scanner->getLine();
        $col = $scanner->getColumn();

        $scanner->advance(2);

        $componentName = $scanner->consumeWhile(fn(string $ch) => ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || $ch === '-' || ($ch >= '0' && $ch <= '9'));
        $isHTMLTag = $componentName === '';

        if ($isHTMLTag) {
            $this->tokens[] = new Token(TokenType::T_HTML, "</{$componentName}", $line, $col);
            return;
        }

        $this->tokens[] = new Token(TokenType::T_COMPONENT_CLOSE, $componentName, $line, $col);
        $scanner->advance();
    }

    private function tokenizeComponent(TemplateScanner $scanner)
    {
        $line = $scanner->getLine();
        $col = $scanner->getColumn();

        $scanner->advance(1);

        $componentName = $scanner->consumeWhile(fn(string $ch) => ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || $ch === '-' || ($ch >= '0' && $ch <= '9'));
        $isHTMLTag = $componentName === '';

        if ($isHTMLTag) {
            $this->tokens[] = new Token(TokenType::T_HTML, "<{$componentName}", $line, $col);
            return;
        }

        $this->tokens[] = new Token(TokenType::T_COMPONENT_OPEN, $componentName, $line, $col);

        $scanner->skipWhitespace();

        if ($this->isComponentAutoClose($scanner)) {
            $this->componentSelfClose($scanner, $componentName);
            return;
        }

        $args = '';
        $quote = null;
        $argLine = $scanner->getLine();
        $argCol = $scanner->getColumn();

        while (!$scanner->isEOF()) {
            $ch = $scanner->currentChar();

            if ($quote !== null) {
                if ($ch === $quote) {
                    $quote = null;
                }

                $args .= $ch;
                $scanner->advance(1);
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $quote = $ch;
                $args .= $ch;
                $scanner->advance(1);
                continue;
            }

            if ($ch === '>') {
                break;
            }

            if ($scanner->peek(2) === '/>') {
                break;
            }

            $args .= $ch;
            $scanner->advance(1);
        }

        $this->tokens[] = new Token(TokenType::T_COMPONENT_PROPS, $args, $argLine, $argCol);

        $this->componentSelfClose($scanner, $componentName);
    }

    private function isComponentAutoClose(TemplateScanner $scanner)
    {
        return $scanner->currentChar() === '>' || $scanner->peek(2) === '/>';
    }

    private function componentSelfClose(TemplateScanner $scanner, string $componentName)
    {
        $scanner->skipWhitespace();

        if ($scanner->peek(2) === '/>') {
            $closeLine = $scanner->getLine();
            $closeCol = $scanner->getColumn();
            $scanner->advance(2);
            $this->tokens[] = new Token(TokenType::T_COMPONENT_SELF_CLOSE, $componentName, $closeLine, $closeCol);
            return;
        }

        $scanner->advance(1);
    }

    private function consumeStaticHtml(TemplateScanner $scanner)
    {
        $line = $scanner->getLine();
        $col = $scanner->getColumn();
        $html = '';

        while (!$scanner->isEOF()) {
            $peek1 = $scanner->peek(1);
            $peek2 = $scanner->peek(2);

            if ($peek1 === '@' || $peek2 === '{{' || $peek1 === '}' || $peek1 === '<') {
                break;
            }

            $html .= $scanner->currentChar();
            $scanner->advance(1);
        }

        if ($html !== '') {
            $this->tokens[] = new Token(TokenType::T_HTML, $html, $line, $col);
        }
    }

    private function tokenizeInterpolation(TemplateScanner $scanner)
    {
        $line = $scanner->getLine();
        $col = $scanner->getColumn();

        $scanner->advance(2);

        $valueBuffer = '';
        while (!$scanner->isEOF()) {
            if ($scanner->peek(2) === '}}') {
                $scanner->advance(2);
                $this->tokens[] = new Token(
                    TokenType::T_INTERPOLATION,
                    trim($valueBuffer),
                    $line,
                    $col
                );
                return;
            }

            $valueBuffer .= $scanner->currentChar();
            $scanner->advance(1);
        }
    }

    private function tokenizeDirective(TemplateScanner $scanner)
    {
        $line = $scanner->getLine();
        $col = $scanner->getColumn();

        $scanner->advance(1);

        $directiveName = $scanner->consumeWhile(fn(string $ch) => ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || $ch === '_');

        if ($directiveName === '') {
            $this->tokens[] = new Token(TokenType::T_HTML, '@', $line, $col);
            return;
        }

        $this->tokens[] = new Token(TokenType::T_DIRECTIVE_NAME, $directiveName, $line, $col);

        if ($scanner->currentChar() === '(') {
            $argLine = $scanner->getLine();
            $argCol = $scanner->getColumn();
            $scanner->advance(1);
            $depth = 1;
            $argBuffer = '';

            while (!$scanner->isEOF() && $depth > 0) {
                $ch = $scanner->currentChar();
                if ($ch === '(') {
                    $depth++;
                } elseif ($ch === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $scanner->advance(1);
                        break;
                    }
                }

                $argBuffer .= $ch;
                $scanner->advance(1);
            }

            $this->tokens[] = new Token(TokenType::T_DIRECTIVE_ARG, \trim($argBuffer), $argLine, $argCol);
        }

        $scanner->skipWhitespace();
        if ($scanner->currentChar() === '{') {
            $this->tokens[] = new Token(TokenType::T_BLOCK_OPEN, '{', $scanner->getLine(), $scanner->getColumn());
            $scanner->advance(1);
        }
    }

    private function tokenizeBlockClose(TemplateScanner $scanner)
    {
        $this->tokens[] = new Token(TokenType::T_BLOCK_CLOSE, '}', $scanner->getLine(), $scanner->getColumn());
        $scanner->advance(1);
    }
}
