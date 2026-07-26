<?php

namespace Diroute\Compiler\Parser\Registry;

use Diroute\Compiler\Generator\Renderer\ElseDirectiveRenderer;
use Diroute\Compiler\Generator\Renderer\ElseIfDirectiveRenderer;
use Diroute\Compiler\Generator\Renderer\EmptyDirectiveRenderer;
use Diroute\Compiler\Generator\Renderer\ForDirectiveRenderer;
use Diroute\Compiler\Generator\Renderer\IfDirectiveRenderer;
use Diroute\Compiler\Parser\Registry\DirectiveConfig;
use InvalidArgumentException;

class DirectiveRegistry
{
    /** @var array<string, DirectiveConfig> */
    private array $directives = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    public function register(DirectiveConfig $config): self
    {
        $this->directives[$config->name] = $config;
        return $this;
    }

    public function get(string $name): DirectiveConfig
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("A diretiva '@{$name}' não foi registrada no DirectiveRegistry.");
        }

        return $this->directives[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->directives[$name]);
    }

    /**
     * Registra as diretivas estruturais padrão do Diroute
     */
    private function registerDefaults(): void
    {
        // @if(logicExpression) { ... } @elseif(...) { ... } @else { ... }
        $this->register(new DirectiveConfig(
            name: 'if',
            hasArguments: true,
            allowedConnections: ['elseif', 'else'],
            rendererClass: IfDirectiveRenderer::class
        ));

        $this->register(new DirectiveConfig(
            name: 'elseif',
            hasArguments: true,
            allowedConnections: ['elseif', 'else'],
            rendererClass: ElseIfDirectiveRenderer::class
        ));

        $this->register(new DirectiveConfig(
            name: 'else',
            hasArguments: false,
            allowedConnections: [],
            rendererClass: ElseDirectiveRenderer::class
        ));

        // @for(users as user) { ... } @empty { ... }
        $this->register(new DirectiveConfig(
            name: 'for',
            hasArguments: true,
            allowedConnections: ['empty'],
            rendererClass: ForDirectiveRenderer::class
        ));

        $this->register(new DirectiveConfig(
            name: 'empty',
            hasArguments: false,
            allowedConnections: [],
            rendererClass: EmptyDirectiveRenderer::class
        ));
    }
}
