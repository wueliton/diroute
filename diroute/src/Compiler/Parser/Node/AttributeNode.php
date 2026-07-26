<?php

namespace Diroute\Compiler\Parser\Node;

class AttributeNode
{
    public function __construct(
        public string $name,
        public ?string $value = null,
        public bool $isBinding = false,
        public bool $isBoolean = false,
    ) {}
}
