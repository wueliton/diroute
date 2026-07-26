<?php

namespace Example\Http;

use Example\Cache\StaticFileCache;

class IsrRouterHandler implements RouterHandlerInterface
{
    public function match(string $uri, string $method): bool
    {
        return StaticFileCache::hasStaticFile($uri);
    }

    public function handle(string $uri, string $method): void
    {
        StaticFileCache::readStaticFile($uri);
    }
}
