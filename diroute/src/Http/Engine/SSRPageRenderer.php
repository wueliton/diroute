<?php

namespace Diroute\Http\Engine;

use Diroute\Cache\RenderCache;
use Diroute\Compiler\CompilerEngine;
use Diroute\Compiler\Runtime\ComponentSSRRenderer;
use Diroute\Compiler\Runtime\TemplateRunner;
use Diroute\Http\Context\ContextHydrator;
use Diroute\Http\Router\RouteMatch;
use RuntimeException;

class SSRPageRenderer
{
    private ContextHydrator $hydrator;

    public function __construct(
        private readonly CompilerEngine $compiler,
        private readonly TemplateRunner $runner,
    ) {
        $this->hydrator = new ContextHydrator();
    }

    public function render(RouteMatch $match): string
    {
        if (!\file_exists($match->filePath)) {
            throw new RuntimeException("Arquivo '{$match->filePath}' não encontrado.");
        }

        require_once $match->filePath;

        $class = $match->controllerClass;
        if (!\class_exists($class)) {
            throw new RuntimeException("Classe '{$class}' não encontrada.");
        }

        $pageInstance = new $class();

        $pageScope = $this->hydrator->hydrate($pageInstance, $match->params);

        // Injeta os metadados do atributo #[Page]
        $pageScope['title'] ??= $match->pageAttribute->title;
        $pageScope['description'] ??= $match->pageAttribute->description;

        // 3. Resolve e compila o template HTML da página
        $templatePath = \dirname($match->filePath) . '/' . $match->pageAttribute->template;
        if (!\file_exists($templatePath)) {
            throw new RuntimeException("Template da página '{$templatePath}' não encontrado.");
        }

        // 4. Executa a página injetando o $componentRenderer para os componentes filhos
        $html = $this->runner->run($templatePath, $pageScope);

        return $html;
    }
}
