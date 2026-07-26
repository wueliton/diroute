<?php

namespace Example\Http;

use Example\Cache\StaticFileCache;
use Example\Compiler\RouteCompiler;
use Example\Router\TrieRouter;
use Example\Runtime\TemplateRenderer;

class FileBasedRouteHandler implements RouterHandlerInterface
{
    private TrieRouter $router;

    public function __construct(private string $pagesDir, private string $compiledRoutesFile, private TemplateRenderer $render)
    {
        if (!is_file($compiledRoutesFile)) {
            $routeCompiler = new RouteCompiler($pagesDir, $compiledRoutesFile);
            $routeCompiler->compile();
        }

        $this->router = require $compiledRoutesFile;
    }

    public function match(string $uri, string $method): bool
    {
        return $this->router->match($uri) !== null;
    }

    public function handle(string $uri, string $method): void
    {
        $result = $this->router->match($uri);

        $pageFile = $result['route']['pageFile'];
        $params = $result['params'];
        $class = $this->getClass($pageFile);

        require_once $pageFile;

        $staticHtml = $this->render->renderInstance(new $class(), $params);
        StaticFileCache::storeFile($uri, $staticHtml);

        echo $staticHtml;
    }

    private function getClass(string $filePath)
    {
        $tokens = token_get_all(file_get_contents($filePath));
        $namespace = '';
        $class = '';
        $gettingNamespace = false;
        $gettingClass = false;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) $gettingNamespace = true;
                if ($token[0] === T_CLASS) $gettingClass = true;

                if ($gettingNamespace && ($token[0] === T_NAME_QUALIFIED || $token[0] === T_STRING)) {
                    $namespace .= $token[1];
                }

                if ($gettingClass && $token[0] === T_STRING) {
                    $class = $token[1];
                    break;
                }
            } else if ($token === ';') {
                $gettingNamespace = false;
            }
        }

        return $class ? ($namespace ? $namespace . '\\' . $class : $class) : null;
    }
}
