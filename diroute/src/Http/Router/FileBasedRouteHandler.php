<?php

namespace Diroute\Http\Router;

class FileBasedRouteHandler implements RouterHandlerInterface
{
    private PathTreeRouter $router;

    public function __construct(
        string $pagesDir,
        string $compiledRoutesFile
    ) {
        $compiler = new FileRouteCompiler($pagesDir, $compiledRoutesFile);
        $rootNode = $compiler->getOrCompile();

        $this->router = new PathTreeRouter($rootNode);
    }

    public function match(string $uri, string $method): bool
    {
        return $this->router->match($uri) !== null;
    }

    public function handle(string $uri, string $method): ?RouteMatch
    {
        return $this->router->match($uri);
    }
}
