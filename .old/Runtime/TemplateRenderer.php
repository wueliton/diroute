<?php

namespace Example\Runtime;

use Example\Annotations\Component;
use Example\Annotations\Page;
use Example\Assets\AssetsCollector;
use Example\Compiler\TemplateCompiler;
use Exception;
use ReflectionClass;
use ReflectionMethod;

class TemplateRenderer
{
    public function __construct(
        private TemplateCompiler $compiler,
        private AssetsCollector $assestCollector
    ) {}

    public function renderInstance(object $instance, array $params = [], string $slotContent = ''): string
    {
        $t1 = microtime(true);
        $reflection = new ReflectionClass($instance);
        $metadata = $this->extractMetada($reflection);
        $classDir = dirname($reflection->getFileName());
        $templatePath = $classDir . '/' . $metadata->template;

        if (!file_exists($templatePath)) {
            throw new Exception("Template '{$metadata->template}' não encontrado em: {$classDir}");
        }

        if (!empty($metadata->styles)) {
            $this->assestCollector->addStyle($classDir . '/' . $metadata->styles);
        }

        if (!empty($metadata->script)) {
            $this->assestCollector->addScript($classDir . '/' . $metadata->script);
        }

        $context = get_object_vars($instance);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            if (str_starts_with($methodName, '__')) {
                continue;
            }

            $context[$methodName] = $method->getClosure($instance);
        }

        $context['slot'] = $slotContent;
        $context['params'] = $params;

        $compiledPhpPath = $this->compiler->getOrCompile($templatePath);

        return $this->evaluateCompiledFile($instance, $compiledPhpPath, $context);
    }

    public function extractMetada(ReflectionClass $reflection): Page|Component
    {
        $attributes = array_merge($reflection->getAttributes(Component::class), $reflection->getAttributes(Page::class));

        if (empty($attributes)) {
            throw new Exception("A class '{$reflection->getName()}' precisa ter #[Component] ou #[Page].");
        }

        return $attributes[0]->newInstance();
    }

    public function evaluateCompiledFile(object $instance, string $compiledPhpPath, array $data): string
    {
        ob_start();
        $renderer = (function () use ($compiledPhpPath, $data) {
            extract($data, EXTR_SKIP);
            include $compiledPhpPath;
        })->bindTo($instance, $instance);

        $renderer();
        return ob_get_clean();
    }
}
