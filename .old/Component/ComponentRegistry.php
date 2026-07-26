<?php

namespace Example\Component;

use Example\Annotations\Component;
use ReflectionClass;

class ComponentRegistry
{
    /** @var array<string, ComponentConfig> */
    private static array $components = [];

    public function __construct() {}

    public function register(string $componentClass): void
    {
        $reflection = new ReflectionClass($componentClass);
        $attributes = $reflection->getAttributes(Component::class);

        if (!empty($attributes)) {
            $componentAttributes = $attributes[0]->newInstance();
            $selector = $componentAttributes->selector;
            self::$components[$selector] = new ComponentConfig($selector, $componentClass);
        }
    }

    public static function isRegistered(string $name): bool
    {
        return isset(self::$components[$name]);
    }

    public static function get(string $name): ?ComponentConfig
    {
        return self::$components[$name] ?? null;
    }
}
