<?php

namespace Diroute\Http\Router;

class PathTreeRouter
{
    public function __construct(
        private readonly RouteNode $rootNode
    ) {}

    public function match(string $requestUri): ?RouteMatch
    {
        // Limpa query strings (ex: /users/123?ref=github -> /users/123)
        $path = \parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $rawSegments = \explode('/', \trim($path, '/'));

        $segments = [];
        foreach ($rawSegments as $s) {
            if ($s !== '') {
                $segments[] = $s;
            }
        }

        $params = [];
        $currentNode = $this->rootNode;

        // PERCURSO ZERO-REGEX: Navega pelos segmentos em tempo O(d)
        foreach ($segments as $segment) {
            $segmentLower = \mb_strtolower($segment);

            // 1. Tenta correspondência exata de nó estático
            if (isset($currentNode->staticChildren[$segmentLower])) {
                $currentNode = $currentNode->staticChildren[$segmentLower];
                continue;
            }

            // 2. Tenta nó dinâmico (ex: {id} ou [id])
            if ($currentNode->dynamicChild !== null) {
                $currentNode = $currentNode->dynamicChild;
                $paramName = $currentNode->paramName ?? 'param';
                $params[$paramName] = $segment;
                continue;
            }

            // Se não encontrou nem nó estático nem dinâmico, a rota não existe (404 instantâneo)
            return null;
        }

        // Verifica se o nó atingido no final do caminho é um endpoint registrado
        if ($currentNode->isEndpoint()) {
            return new RouteMatch(
                controllerClass: $currentNode->controllerClass,
                pageAttribute: $currentNode->pageAttribute,
                filePath: $currentNode->filePath,
                params: $params
            );
        }

        return null;
    }
}
