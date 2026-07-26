<?php

namespace Example\Router;

class TrieRouter
{
    private TrieNode $root;

    public function __construct(?TrieNode $root = null)
    {
        $this->root = $root ?? new TrieNode();
    }

    public static function __set_state(array $properties): static
    {
        return new static($properties['root'] ?? new TrieNode());
    }

    public function addRoute(string $pathPattern, array $routeData): void
    {
        $segments = array_filter(explode('/', trim($pathPattern, '/')));
        $currentNode = $this->root;

        foreach ($segments as $segment) {
            if (str_starts_with($segment, '[') && str_ends_with($segment, ']')) {
                $paramName = substr($segment, 1, -1);

                if ($currentNode->paramChild === null) {
                    $currentNode->paramChild = new TrieNode();
                    $currentNode->paramName = $paramName;
                }
                $currentNode = $currentNode->paramChild;
            } else {
                if (!isset($currentNode->staticChildren[$segment])) {
                    $currentNode->staticChildren[$segment] = new TrieNode();
                }
                $currentNode = $currentNode->staticChildren[$segment];
            }
        }

        $currentNode->routeData = $routeData;
    }

    public function match(string $uri): ?array
    {
        $pathWithoutParams = strtok($uri, '?');
        $segments = array_filter(explode('/', trim($pathWithoutParams, '/')));
        $currentNode = $this->root;
        $params = [];

        foreach ($segments as $segment) {
            if (isset($currentNode->staticChildren[$segment])) {
                $currentNode = $currentNode->staticChildren[$segment];
            } elseif ($currentNode->paramChild !== null) {
                $params[$currentNode->paramName] = $segment;
                $currentNode = $currentNode->paramChild;
            } else {
                return null;
            }
        }

        if ($currentNode->routeData === null) {
            return null;
        }

        return [
            'route' => $currentNode->routeData,
            'params' => $params
        ];
    }
}
