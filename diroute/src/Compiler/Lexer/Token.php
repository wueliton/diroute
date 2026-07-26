<?php

namespace Diroute\Compiler\Lexer;

use Diroute\Compiler\Contract\TokenInterface;

readonly class Token implements TokenInterface
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public int $line,
        public int $column
    ) {}

    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}
