<?php

namespace Diroute\Http\Router;

interface RouterHandlerInterface
{
    public function match(string $uri, string $method): bool;

    /**
     * Resolve a URI e entrega os metadados necessários para o App rodar a renderização.
     */
    public function handle(string $uri, string $method): ?RouteMatch;
}
