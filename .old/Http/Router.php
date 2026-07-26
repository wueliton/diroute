<?php

namespace Example\Http;

use Example\Http\RouterHandlerInterface;

class Router
{
    /** @var RouterHandlerInterface[] */
    private array $handlers = [];

    public function addHandle(RouterHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->handlers as $handler) {
            if ($handler->match($uri, $method)) {
                $handler->handle($uri, $method);
                return;
            }
        }

        $this->sendNotFoundResponse();
    }

    private function sendNotFoundResponse()
    {
        http_response_code(404);
        echo '<h1>404 - Not Found</h1><p>Nenhuma rota do Diroute respondeu por este caminho.</p>';
    }
}
