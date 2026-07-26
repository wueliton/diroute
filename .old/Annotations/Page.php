<?php

namespace Example\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Page
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $template = null,
        public ?string $styles = null,
        public ?string $script = null,
        public ?int $revalidate = 0,
    ) {}
}
