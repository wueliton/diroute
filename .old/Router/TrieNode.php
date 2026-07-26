<?php

namespace Example\Router;

class TrieNode
{
    /** @var array<string, TrieNode> */
    public array $staticChildren = [];
    public ?TrieNode $paramChild = null;
    public ?string $paramName = null;
    public ?array $routeData = null;

    public static function __set_state(array $properties): static
    {
        $node = new static();
        $node->staticChildren = $properties['staticChildren'] ?? [];
        $node->paramChild = $properties['paramChild'] ?? null;
        $node->paramName = $properties['paramName'] ?? null;
        $node->routeData = $properties['routeData'] ?? null;
        return $node;
    }
}
