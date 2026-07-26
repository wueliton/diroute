<?php

namespace Diroute\Compiler\Lexer;

class TemplateScanner
{
    private int $cursor = 0;
    private int $line = 1;
    private int $column = 1;
    private readonly int $length;

    public function __construct(private readonly string $source)
    {
        $this->length = strlen($source);
    }

    public function isEOF(): bool
    {
        return $this->cursor >= $this->length;
    }

    public function getCursor(): int
    {
        return $this->cursor;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function currentChar(): string
    {
        return $this->source[$this->cursor] ?? '';
    }

    public function peek(int $length = 1, int $offset = 0): string
    {
        $pos = $this->cursor + $offset;
        if ($pos >= $this->length) {
            return '';
        }
        return substr($this->source, $pos, $length);
    }

    public function advance(int $steps = 1): void
    {
        for ($i = 0; $i < $steps; $i++) {
            if ($this->cursor >= $this->length) {
                break;
            }

            if ($this->source[$this->cursor] === "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }

            $this->cursor++;
        }
    }

    public function consumeWhile(callable $predicate): string
    {
        $start = $this->cursor;
        while (!$this->isEOF() && $predicate($this->source[$this->cursor])) {
            $this->advance(1);
        }
        return substr($this->source, $start, $this->cursor - $start);
    }

    public function skipWhitespace(): void
    {
        while (!$this->isEOF() && ctype_space($this->source[$this->cursor])) {
            $this->advance(1);
        }
    }
}
