<?php

namespace Example\Component;

class ComponentConfig
{
    public function __construct(
        public string $name,
        public string $renderClass
    ) {}
}
