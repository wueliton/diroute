<?php

namespace Example\Parser;

use Example\Parser\TokenType;
use Example\Parser\Token;

class TokenStream
{
    /** @var array<int, Token> */
    private array $tokens;
    private int $position = 0;
    private int $count;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->count = count($tokens);
    }

    public function isAtEnd()
    {
        return $this->position === $this->count;
    }

    public function consume(): ?Token
    {
        if ($this->position >= $this->count) {
            return null;
        }
        return $this->tokens[$this->position++];
    }

    public function peek(int $offset = 0): ?Token
    {
        $target = $this->position + $offset;
        if ($target >= $this->count || $target < 0) {
            return null;
        }

        return $this->tokens[$target];
    }

    public function match(TokenType $tokenType)
    {
        $token = $this->peek();
        return $token !== null && $token->type == $tokenType;
    }

    public function tokens()
    {
        return $this->tokens;
    }
}
