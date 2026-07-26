<?php

namespace Diroute\Compiler\Runtime;

use Closure;
use Diroute\Compiler\CompilerEngine;
use Diroute\Http\Context\ContextHydrator;
use Diroute\Http\Registry\ComponentRegistry;
use RuntimeException;

class ComponentSSRRenderer
{
    private ContextHydrator $hydrator;

    public function __construct(
        private readonly ComponentRegistry $componentRegistry,
        private readonly TemplateRunner $runner
    ) {
        $this->hydrator = new ContextHydrator();
    }

    /**
     * Renderiza e hidrata um componente decorado com #[Component]
     *
     * @param string $selector Seletor do componente (ex: 'app-button')
     * @param array<string, mixed> $passedProps Props passadas na tag
     * @param Closure $slotRenderer Closure que renderiza o conteúdo interno (<slot />)
     * @return string HTML final renderizado
     */
    public function render(string $selector, array $passedProps, Closure $slotRenderer): string
    {
        // 1. Busca os metadados do #[Component] no ComponentRegistry
        if (!$this->componentRegistry->has($selector)) {
            throw new RuntimeException("Componente com o seletor '<{$selector}>' não está registrado.");
        }

        $compData = $this->componentRegistry->get($selector);
        $componentClass = $compData['class'];
        $attribute = $compData['attribute'];
        $directory = $compData['dir'];

        // 2. Instancia a classe do Componente (executando o construtor e D.I. se houver)
        $componentInstance = new $componentClass();

        // 3. Captura o HTML do Slot/Children enviado pela página pai
        \ob_start();
        $slotRenderer();
        $slotHtml = \ob_get_clean() ?: '';

        // 4. Hidrata o contexto do componente (Atributos + Métodos + Props passadas + Slot)
        $componentScope = $this->hydrator->hydrateComponent($componentInstance, $passedProps);
        $componentScope['slot'] = $slotHtml;

        // 5. Carrega o template do componente
        $templatePath = $directory . '/' . $attribute->template;
        if (!\file_exists($templatePath)) {
            throw new RuntimeException("Template do componente '{$templatePath}' não foi encontrado.");
        }

        // 7. Avalia no TemplateRunner com escopo estritamente ISOLADO
        return $this->runner->run($templatePath, $componentScope);
    }
}
