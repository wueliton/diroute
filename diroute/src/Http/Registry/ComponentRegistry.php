<?php

namespace Diroute\Http\Registry;

use Diroute\Http\Attribute\Component;
use ReflectionClass;
use RuntimeException;

class ComponentRegistry
{
    /** 
     * @var array<string, array{class: string, attribute: Component, dir: string}> 
     */
    private array $components = [];

    /**
     * Registra um componente globalmente a partir da sua FQCN (Class Name).
     *
     * @param string $componentClass Ex: App\Components\ButtonComponent::class
     */
    public function register(string $componentClass): self
    {
        if (!\class_exists($componentClass)) {
            throw new RuntimeException("A classe do componente '{$componentClass}' não foi encontrada.");
        }

        $reflection = new ReflectionClass($componentClass);
        $attributes = $reflection->getAttributes(Component::class);

        if (empty($attributes)) {
            throw new RuntimeException("A classe '{$componentClass}' não possui o atributo #[Component].");
        }

        /** @var Component $componentAttr */
        $componentAttr = $attributes[0]->newInstance();

        $this->components[$componentAttr->selector] = [
            'class' => $componentClass,
            'attribute' => $componentAttr,
            'dir' => \dirname($reflection->getFileName() ?: ''),
        ];

        return $this;
    }

    /**
     * Registra múltiplos componentes de uma só vez.
     * 
     * @param array<int, string> $componentClasses
     */
    public function registerMany(array $componentClasses): self
    {
        foreach ($componentClasses as $componentClass) {
            $this->register($componentClass);
        }

        return $this;
    }

    public function has(string $selector): bool
    {
        return isset($this->components[$selector]);
    }

    public function get(string $selector): array
    {
        if (!$this->has($selector)) {
            throw new RuntimeException("Nenhum componente registrado para o seletor '<{$selector}>'.");
        }

        return $this->components[$selector];
    }

    public function getAll(): array
    {
        return $this->components;
    }
}
