<?php

namespace Example\Http;

interface RouterHandlerInterface
{
    public function match(string $uri, string $method): bool;

    public function handle(string $uri, string $method): void;
}
