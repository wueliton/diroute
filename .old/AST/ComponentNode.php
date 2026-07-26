<?php

namespace Example\AST;

class ComponentNode
{
    public function __construct(
        public string $name,
        /** @var array<string, AttributeNode> */
        public array $attributes,
        public array $body,
    ) {}
}
