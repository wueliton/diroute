<?php

namespace Example\Http;

use Override;

class InternalRouterHandler implements RouterHandlerInterface
{
    public function __construct(private string $cacheDir) {}

    public function match(string $uri, string $method): bool
    {
        return str_starts_with($uri, '/_diroute/');
    }

    #[Override]
    public function handle(string $uri, string $method): void
    {
        if ($uri === '/_diroute/dev-reload') {
            $controller = new DevReloadController($this->cacheDir);
            $controller->handle();
            return;
        }

        http_response_code(404);
        print_r(json_encode(['message' => 'Not found']));
    }
}
