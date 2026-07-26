<?php

namespace Example\Directive;

use Example\Directive\DirectiveConfig;
use Example\Directive\Renderers\ElseDirectiveRenderer;
use Example\Directive\Renderers\ElseIfDirectiveRenderer;
use Example\Directive\Renderers\EmptyDirectiveRenderer;
use Example\Directive\Renderers\ForDirectiveRenderer;
use Example\Directive\Renderers\IfDirectiveRenderer;

class DirectiveRegistry
{
    /** @var array<string, DirectiveConfig> */
    private static array $directives = [];
    private static bool $initialized = false;

    public static function register(DirectiveConfig $config)
    {
        self::$directives[$config->name] = $config;
    }

    public static function get(string $name): ?DirectiveConfig
    {
        return self::$directives[$name] ?? null;
    }

    public static function isRegistered(string $name): bool
    {
        return isset(self::$directives[$name]);
    }

    public static function boot()
    {
        if (self::$initialized) return;

        self::$initialized = true;

        self::$directives = [
            'if' => new DirectiveConfig(
                name: "if",
                hasArguments: true,
                allowedConnections: ['elseif', 'else'],
                rendererClass: IfDirectiveRenderer::class
            ),
            'elseif' => new DirectiveConfig(
                name: 'elseif',
                hasArguments: true,
                allowedConnections: ['elseif', 'else'],
                rendererClass: ElseIfDirectiveRenderer::class
            ),
            'else' => new DirectiveConfig(
                name: 'else',
                hasArguments: false,
                allowedConnections: [],
                rendererClass: ElseDirectiveRenderer::class
            ),
            'for' => new DirectiveConfig(
                name: 'for',
                hasArguments: true,
                allowedConnections: ['empty'],
                rendererClass: ForDirectiveRenderer::class
            ),
            'empty' => new DirectiveConfig(
                name: 'empty',
                hasArguments: false,
                allowedConnections: [],
                rendererClass: EmptyDirectiveRenderer::class
            )
        ];
    }
}
