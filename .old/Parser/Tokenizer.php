<?php

namespace Example\Parser;

use Example\Parser\Token;
use Example\Parser\TokenStream;

class Tokenizer
{
    private string $template;
    private array $tokens = [];
    private int $cursor = 0;
    private int $length = 0;
    private int $line = 1;
    private string $pattern = '@}{<';

    public function tokenize(string $template)
    {
        $this->template = $template;
        $this->tokens = [];
        $this->cursor = 0;
        $this->length = strlen($template);
        $this->line = 1;

        while ($this->cursor < $this->length) {
            if (!$this->processNextToken()) {
                break;
            }
        }

        $this->tokens[] = new Token(TokenType::EOF, '', $this->line);
        return new TokenStream($this->tokens);
    }

    private function processNextToken(): bool
    {
        $nextTrigger = strpbrk(substr($this->template, $this->cursor), $this->pattern);

        if ($nextTrigger === false) {
            $this->processRemainingText();
            return false;
        }

        $triggerPos = $this->cursor + strcspn($this->template, $this->pattern, $this->cursor);
        $this->processPreviousText($triggerPos);

        $char = $this->template[$this->cursor];

        if ($this->isComment()) {
            $this->processComment();
        } elseif ($this->isExpression()) {
            $this->processExpression();
        } elseif ($char === '@') {
            $this->processDirective();
        } elseif ($char === '{') {
            $this->addToken(TokenType::SCOPE_START, '{');
            $this->cursor++;
        } elseif ($char === '}') {
            $this->addToken(TokenType::SCOPE_END, '}');
            $this->cursor++;
        } elseif ($char === '<') {
            $this->processComponent();
        } else {
            $this->cursor++;
        }

        return true;
    }

    private function processRemainingText(): void
    {
        $text = substr($this->template, $this->cursor);
        if ($text !== '') {
            $this->addToken(TokenType::TEXT, $text);
            $this->line += substr_count($text, "\n");
        }
    }

    private function processPreviousText(int $triggerPos): void
    {
        if ($triggerPos > $this->cursor) {
            $text = substr($this->template, $this->cursor, $triggerPos - $this->cursor);
            $this->addToken(TokenType::TEXT, $text);
            $this->line += substr_count($text, "\n");
            $this->cursor = $triggerPos;
        }
    }

    private function isComment(): bool
    {
        return $this->isHtmlComment() || $this->isPhpComment();
    }

    private function isHtmlComment(): bool
    {
        return substr($this->template, $this->cursor, 4) === '<!--';
    }

    private function isPhpComment(): bool
    {
        return substr($this->template, $this->cursor, 4) === '{{--'
            || substr($this->template, $this->cursor, 2) === '<?';
    }

    private function processComment(): void
    {
        if ($this->isHtmlComment()) {
            $this->skipUntilDelimiter('-->', 3);
        } elseif (substr($this->template, $this->cursor, 4) === '{{--') {
            $this->skipUntilDelimiter('--}}', 4);
        } elseif (substr($this->template, $this->cursor, 2) === '<?') {
            $this->skipUntilDelimiter('?>', 2);
        }
    }

    private function skipUntilDelimiter(string $delimiter, int $delimiterLength): void
    {
        $endPos = strpos($this->template, $delimiter, $this->cursor);
        if ($endPos === false) {
            $commentText = substr($this->template, $this->cursor);
            $this->line += substr_count($commentText, "\n");
            $this->cursor = $this->length;
        } else {
            $commentText = substr($this->template, $this->cursor, $endPos + $delimiterLength - $this->cursor);
            $this->line += substr_count($commentText, "\n");
            $this->cursor = $endPos + $delimiterLength;
        }
    }

    private function isExpression(): bool
    {
        $startExpression = substr($this->template, $this->cursor, 2);
        $endExpression = strpos($this->template, '}}', $this->cursor);
        return $startExpression === '{{' && $endExpression !== false;
    }

    private function processExpression(): void
    {
        $endExpression = strpos($this->template, '}}', $this->cursor);
        $expression = str_replace(['{{', '}}'], '', substr($this->template, $this->cursor, $endExpression - $this->cursor));
        $this->addToken(TokenType::EXPRESSION, $expression);
        $this->cursor = $endExpression + 2;
    }

    private function processDirective(): void
    {
        $peekPos = $this->cursor + 1;
        $nameLength = strspn($this->template, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_', $peekPos);

        if ($this->isInvalidDirective($nameLength, $this->cursor)) {
            $this->addToken(TokenType::TEXT, '@');
            $this->cursor++;
            return;
        }

        $directiveName = substr($this->template, $peekPos, $nameLength);
        $this->addToken(TokenType::DIRECTIVE_NAME, $directiveName);
        $this->cursor = $peekPos + $nameLength;

        $this->processDirectiveArguments();
        $this->skipWhitespace();
        $this->processDirectiveScope();
    }

    private function isInvalidDirective(int $nameLength, int $cursor): bool
    {
        $emptyName = $nameLength === 0;
        $prevCharPos = $cursor - 1;
        $prevChar = $this->template[$prevCharPos] ?? null;
        $invalidContext = $prevChar && !ctype_space($prevChar);

        return $emptyName || $invalidContext;
    }

    private function processDirectiveArguments(): void
    {
        if ($this->cursor < $this->length && $this->template[$this->cursor] === '(') {
            $this->cursor++;
            $conditionPos = strpos($this->template, ')', $this->cursor);
            $args = substr($this->template, $this->cursor, $conditionPos - $this->cursor);
            $this->addToken(TokenType::ARGUMENTS, $args);
            $this->cursor = $conditionPos + 1;
        }
    }

    private function skipWhitespace(): void
    {
        $this->cursor += strspn($this->template, " \t\r\n", $this->cursor);
    }

    private function processDirectiveScope(): void
    {
        if ($this->cursor < $this->length && $this->template[$this->cursor] === '{') {
            $this->addToken(TokenType::SCOPE_START, '{');
            $this->cursor++;
        }
    }

    private function addToken(TokenType $type, string $value): void
    {
        $this->tokens[] = new Token($type, $value, $this->line);
    }

    private function processComponent()
    {
        $peekPos = $this->cursor + 1;
        $nextChar = $this->template[$peekPos];
        $tokenType = TokenType::COMPONENT_START;

        if ($nextChar === '/') {
            $peekPos++;
            $tokenType = TokenType::COMPONENT_END;
        }

        $nameLength = strspn($this->template, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-', $peekPos);
        $componentName = substr($this->template, $peekPos, $nameLength);

        $nextCharAfterName = $this->template[$peekPos + $nameLength];
        $hasAttributes = $tokenType === TokenType::COMPONENT_START && $nextCharAfterName === " ";

        $this->addToken($tokenType, $componentName);
        $this->cursor = $peekPos + $nameLength + 1;

        if ($hasAttributes) {
            $this->processAttributes();
        }
    }

    private function processAttributes()
    {
        $insideComma = false;
        $initialPosition = $this->cursor;

        while (true) {
            $nextChar = $this->template[$this->cursor];
            if ($nextChar === '"' || $nextChar === "'") {
                $insideComma = !$insideComma;
            }

            $endTag = $nextChar === '>' && !$insideComma;
            if ($endTag) {
                break;
            }

            $this->cursor++;
        }

        $length = $this->cursor - $initialPosition;
        $attributes = substr($this->template, $initialPosition, $length);
        $this->addToken(TokenType::ATTRIBUTES, $attributes);
        $this->cursor++;
    }
}
