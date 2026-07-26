<?php

namespace Diroute\Http\Router;

use Diroute\Http\Attribute\Page;
use Diroute\Http\Router\RouteNode;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class FileRouteCompiler
{
    public function __construct(
        private readonly string $pagesDir,
        private readonly string $cacheFile
    ) {}

    /**
     * Retorna o nó raiz da PathTree carregado do cache ou re-compilado se houver mudanças.
     */
    public function getOrCompile(): RouteNode
    {
        if (\file_exists($this->cacheFile)) {
            /** @var RouteNode $rootNode */
            $rootNode = require $this->cacheFile;

            // Checagem ultra-rápida: Valida se algum arquivo na pasta foi modificado após o cache
            if (!$this->hasFilesBeenModified($rootNode->mtime)) {
                return $rootNode;
            }
        }

        // Se o cache não existe ou está desatualizado, re-compila a árvore
        return $this->compile();
    }

    public function compile(): RouteNode
    {
        $root = new RouteNode();
        $root->mtime = \time();

        if (!\is_dir($this->pagesDir)) {
            return $root;
        }

        $directory = new RecursiveDirectoryIterator($this->pagesDir);
        $iterator = new RecursiveIteratorIterator($directory);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $className = $this->getClassFromFile($filePath);

            if ($className === null) {
                continue;
            }

            if (!\class_exists($className)) {
                require_once $filePath;
            }

            if (!\class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            $attributes = $reflection->getAttributes(Page::class);

            // Filtra exclusivamente arquivos que possuem o decorator #[Page]
            foreach ($attributes as $attribute) {
                /** @var Page $pageAttr */
                $pageAttr = $attribute->newInstance();

                $relativeDir = \ltrim(\substr($file->getPath(), \strlen($this->pagesDir)), '/\\');
                $segments = $this->buildSegmentsFromPath($relativeDir, $file->getBasename('.php'));

                $this->insertRoute($root, $segments, $className, $pageAttr, $filePath, $file->getMTime());
            }
        }

        // Grava a árvore compilada no arquivo de cache utilizando var_export/PHP nativo
        $this->saveCache($root);

        return $root;
    }

    private function insertRoute(
        RouteNode $root,
        array $segments,
        string $controllerClass,
        Page $pageAttr,
        string $filePath,
        int $mtime
    ): void {
        $currentNode = $root;

        foreach ($segments as $segment) {
            // Identifica segmentos dinâmicos ex: {id} ou [id]
            if (\preg_match('/^[\{\[]([a-zA-Z0-9_]+)[\}\]]$/', $segment, $matches)) {
                if ($currentNode->dynamicChild === null) {
                    $currentNode->dynamicChild = new RouteNode();
                    $currentNode->dynamicChild->paramName = $matches[1];
                }
                $currentNode = $currentNode->dynamicChild;
            } else {
                $segmentLower = \mb_strtolower($segment);
                if (!isset($currentNode->staticChildren[$segmentLower])) {
                    $currentNode->staticChildren[$segmentLower] = new RouteNode();
                }
                $currentNode = $currentNode->staticChildren[$segmentLower];
            }
        }

        // Define este nó como um endpoint de rota válido
        $currentNode->controllerClass = $controllerClass;
        $currentNode->pageAttribute = $pageAttr;
        $currentNode->filePath = $filePath;
        $currentNode->mtime = $mtime;
    }

    private function buildSegmentsFromPath(string $relativeDir, string $fileName): array
    {
        $segments = [];

        if ($relativeDir !== '') {
            $pathSegments = \explode('/', \str_replace('\\', '/', $relativeDir));
            foreach ($pathSegments as $s) {
                if ($s !== '') {
                    $segments[] = $s;
                }
            }
        }

        // Se o nome do arquivo não for 'index', adiciona como segmento final da URI
        $fileLower = \mb_strtolower($fileName);
        if ($fileLower !== 'index' && $fileLower !== 'page') {
            $segments[] = $fileName;
        }

        return $segments;
    }

    private function hasFilesBeenModified(int $cacheTime): bool
    {
        $directory = new RecursiveDirectoryIterator($this->pagesDir);
        $iterator = new RecursiveIteratorIterator($directory);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                if ($file->getMTime() > $cacheTime) {
                    return true; // Arquivo foi alterado! Invalida cache.
                }
            }
        }

        return false;
    }

    private function saveCache(RouteNode $root): void
    {
        $cacheDir = \dirname($this->cacheFile);
        if (!\is_dir($cacheDir)) {
            \mkdir($cacheDir, 0755, true);
        }

        $exported = \serialize($root);
        $code = "<?php\nreturn unserialize(" . \var_export($exported, true) . ");\n";

        \file_put_contents($this->cacheFile, $code, LOCK_EX);
    }

    private function getClassFromFile(string $filePath): ?string
    {
        $tokens = \token_get_all(\file_get_contents($filePath));
        $namespace = '';
        $class = '';
        $gettingNamespace = false;
        $gettingClass = false;

        foreach ($tokens as $token) {
            if (\is_array($token)) {
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
