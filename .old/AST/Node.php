<?php

namespace Example\AST;

class Node
{
    public string $type;
    public mixed $value;
    public array $children;
    public array $attributes;
    public ?string $name;
    public ?string $condition;

    public function __construct(
        string $type,
        mixed $value = null,
        array $children = [],
        array $attributes = [],
        ?string $name = null,
        ?string $condition = null
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->children = $children;
        $this->attributes = $attributes;
        $this->name = $name;
        $this->condition = $condition;
    }
}
