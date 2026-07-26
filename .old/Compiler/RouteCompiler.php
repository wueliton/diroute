<?php

namespace Example\Compiler;

use Example\Router\TrieRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RouteCompiler
{
    public function __construct(private string $pagesDir, private string $compiledRouteFile) {}

    public function compile(): void
    {
        $router = new TrieRouter();

        if (!is_dir($this->pagesDir)) return;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->pagesDir, RecursiveDirectoryIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'page.php') {
                $fullPath = $file->getPathname();
                $routePattern = $this->extractRoutePattern($fullPath);
                $layouts = $this->resolveLayoutsChain(dirname($fullPath));

                $router->addRoute($routePattern, [
                    'pageFile' => $fullPath,
                    'layouts' => $layouts
                ]);
            }
        }

        $this->saveCompiledFile($router);
    }

    private function extractRoutePattern(string $fullPath): string
    {
        $relative = str_replace([$this->pagesDir, '/page.php', '\\page.php', 'page.php'], '', $fullPath);
        $relative = str_replace('\\', '/', $relative);
        $pattern = trim($relative, '/');
        return '/' . $pattern;
    }

    private function resolveLayoutsChain(string $currentDir): array
    {
        $layouts = [];
        $dir = $currentDir;
        $realPagesDir = realpath($this->pagesDir);

        while ($dir && str_starts_with(realpath($dir) ?: '', $realPagesDir)) {
            $layoutFile = $dir . '/layout.php';
            if (file_exists($layoutFile)) {
                array_unshift($layouts, $layoutFile);
            }

            $parentDir = dirname($dir);
            if ($parentDir === $dir) break;
            $dir = $parentDir;
        }

        return $layouts;
    }

    private function saveCompiledFile(TrieRouter $router): void
    {
        $dir = dirname($this->compiledRouteFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $exported = var_export($router, true);

        $code = <<<PHP
<?php
// Arquivo gerado automaticamente pelo Diroute
// NÃO EDITE ESSE ARQUIVO MANUALMENTE

use Example\Routing\TrieRouter;
use Example\Routing\TrieNode;

return {$exported};
PHP;
        file_put_contents($this->compiledRouteFile, $code);
    }
}
