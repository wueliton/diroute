<?php

namespace Example\Directive;

class DirectiveConfig
{
    public function __construct(
        public string $name,
        public bool $hasArguments,
        public array $allowedConnections,
        /** @var DirectiveRenderer */
        public string $rendererClass
    ) {}
}
