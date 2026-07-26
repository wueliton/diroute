<?php

namespace Diroute\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Page
{
    public function __construct(
        public string $title,
        public string $description = '',
        public string $template = 'page.template.html', // Convenção padrão Next-style
        public int $revalidate = 0                     // Revalidação ISR em segundos (0 = No cache)
    ) {}
}
