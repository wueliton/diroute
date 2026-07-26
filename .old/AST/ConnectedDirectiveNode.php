<?php

namespace Example\AST;

class ConnectedDirectiveNode
{
    public function __construct(
        public string $name,
        public ?string $arguments,
        public array $body,
    ) {}
}
