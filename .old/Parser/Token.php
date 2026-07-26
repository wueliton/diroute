<?php

namespace Example\Parser;

class Token
{
    public function __construct(public TokenType $type, public string $text, public int $line) {}
}
