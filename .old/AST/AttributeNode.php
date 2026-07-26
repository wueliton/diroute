<?php

namespace Example\AST;

class AttributeNode
{
    public function __construct(
        public string $name,
        public mixed $value
    ) {}
}
