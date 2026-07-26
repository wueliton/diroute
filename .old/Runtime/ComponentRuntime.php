<?php

namespace Example\Runtime;

use Example\Component\ComponentRegistry;
use Exception;

class ComponentRuntime
{
    private static ?TemplateRenderer $renderer = null;

    public static function setRenderer(TemplateRenderer $renderer): void
    {
        self::$renderer = $renderer;
    }

    public static function render(
        string $nameOrClass,
        array $props = [],
        ?callable $bodyRenderer = null,
        array $parentVars = []
    ): string {
        if (ComponentRegistry::isRegistered($nameOrClass)) {
            $renderClass = ComponentRegistry::get($nameOrClass)->renderClass;
        } else {
            $renderClass = $nameOrClass;
        }

        if (!class_exists($renderClass)) {
            throw new Exception("Componente ou Classe '{$renderClass}' não encontrado.");
        }

        $instance = new $renderClass();

        foreach ($props as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        $slotContent = '';
        if ($bodyRenderer !== null) {
            ob_start();
            $bodyRenderer($parentVars);
            $slotContent = ob_get_clean();
        }

        return self::$renderer->renderInstance($instance, [], $slotContent);
    }
}
