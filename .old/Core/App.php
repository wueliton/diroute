<?php

namespace Example\Core;

use Example\Assets\AssetsCollector;
use Example\Cache\Cache;
use Example\Cache\StaticFileCache;
use Example\Compiler\TemplateCompiler;
use Example\Component\ComponentRegistry;
use Example\Http\FileBasedRouteHandler;
use Example\Http\InternalRouterHandler;
use Example\Http\IsrRouterHandler;
use Example\Http\Router;
use Example\Runtime\ComponentRuntime;
use Example\Runtime\TemplateRenderer;

class App
{
    private ComponentRegistry $componentRegistry;

    public function __construct(private string $basePath)
    {
        $this->componentRegistry = new ComponentRegistry();
        return $this;
    }

    public function addComponent(string $className)
    {
        $this->componentRegistry->register($className);
        return $this;
    }

    public function run()
    {
        $this->setupRouter(dirname(__DIR__, 3) . '/.cache');
        return $this;
    }

    private function setupRouter(string $cacheDir)
    {
        StaticFileCache::setCacheDir($cacheDir);
        $compiledRouteFile = $cacheDir . '/pages.php';
        $router = new Router();
        $cache = new Cache($cacheDir);
        $templateCompiler = new TemplateCompiler($cache);
        $assets = new AssetsCollector();
        $templateRender = new TemplateRenderer($templateCompiler, $assets);
        ComponentRuntime::setRenderer($templateRender);

        $router->addHandle(new InternalRouterHandler($cacheDir));
        $router->addHandle(new IsrRouterHandler($cacheDir));
        $router->addHandle(new FileBasedRouteHandler($this->basePath . '/pages/', $compiledRouteFile, $templateRender));
        if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD'])) {
            $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        }
    }
}
