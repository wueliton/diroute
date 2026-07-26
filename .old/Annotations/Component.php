<?php

namespace Example\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Component
{
    public function __construct(
        public string $selector,
        public ?string $template = null,
        public ?string $styles = null,
        public ?string $script = null,
        public ?array $providers = [],
    ) {}
}
