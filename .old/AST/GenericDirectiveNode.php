<?php

namespace Example\AST;

class GenericDirectiveNode
{
    public function __construct(
        public string $name,
        public ?string $arguments,
        public array $body,
        public array $connections = [],
        public ?GenericDirectiveNode $parent = null
    ) {}

    public function addConnection(GenericDirectiveNode $connection)
    {
        $connection->parent = $this;
        $this->connections[] = $connection;
    }
}
