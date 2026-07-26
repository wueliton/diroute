<?php

namespace Diroute;

use Diroute\Compiler\Cache\CompiledTemplateCache;
use Diroute\Compiler\CompilerEngine;
use Diroute\Compiler\Runtime\ComponentSSRRenderer;
use Diroute\Compiler\Runtime\TemplateRunner;
use Diroute\Http\Engine\SSRPageRenderer;
use Diroute\Http\Registry\ComponentRegistry;
use Diroute\Http\Response\HtmlResponse;
use Diroute\Http\Router\FileBasedRouteHandler;
use Diroute\Profiler\Profiler;

class Application
{
    private ComponentRegistry $componentRegistry;
    private FileBasedRouteHandler $routeHandler;
    private SSRPageRenderer $renderer;

    public function __construct(
        string $pagesDir,
        private ?Profiler $profiler = null,
        string $compiledRoutesFile = __DIR__ . '/../storage/framework/compiled_routes.php',
        private string $cacheDir = __DIR__ . '/../.temp/templates'
    ) {
        $this->componentRegistry = new ComponentRegistry();
        $this->routeHandler = new FileBasedRouteHandler($pagesDir, $compiledRoutesFile);
    }

    /**
     * Permite o registro global de componentes por FQCN Class
     */
    public function registerComponents(array $componentClasses): self
    {
        $this->componentRegistry->registerMany($componentClasses);
        return $this;
    }

    public function handle(string $uri, string $method = 'GET'): HtmlResponse
    {
        // 1. O Roteador faz APENAS o match da rota e retorna o DTO
        $match = $this->routeHandler->handle($uri, $method);

        if ($match === null) {
            return new HtmlResponse(
                htmlConteudo: '<h1>404 - Página Não Encontrada</h1>',
                statusCode: 404
            );
        }

        // 2. Prepara a pipeline de compilação AST com os componentes globais
        $compilerEngine = new CompilerEngine(componentRegistry: $this->componentRegistry, profiler: $this->profiler);
        $templateCache = new CompiledTemplateCache(compiler: $compilerEngine, cacheDir: $this->cacheDir, profiler: $this->profiler);
        $templateRunner = new TemplateRunner(templateCache: $templateCache, profiler: $this->profiler);
        $componentRenderer = new ComponentSSRRenderer(
            $this->componentRegistry,
            $templateRunner
        );
        $templateRunner->setComponentRenderer($componentRenderer);
        $this->renderer = new SSRPageRenderer($compilerEngine, $templateRunner);

        // 3. A pipeline de renderização compila e gera o HTML
        $htmlOutput = $this->renderer->render($match);

        // 4. Retorna o HtmlResponse PSR-7 com suporte a revalidate (ISR)
        return new HtmlResponse(
            htmlConteudo: $htmlOutput,
            statusCode: 200,
            revalidate: $match->pageAttribute->revalidate
        );
    }
}
