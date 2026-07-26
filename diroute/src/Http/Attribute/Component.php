<?php

namespace Diroute\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Component
{
    public function __construct(
        public string $selector,                       // Ex: 'app-button' ou 'x-card'
        public string $template = 'component.html',
        public ?string $styles = null
    ) {}
}
