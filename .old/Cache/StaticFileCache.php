<?php

namespace Example\Cache;

class StaticFileCache
{
    private static string $cacheDir;

    public static function getFilePathByUri(string $uri)
    {
        return self::$cacheDir . '/' . md5($uri) . '.html';
    }

    public static function hasStaticFile(string $uri): bool
    {
        return file_exists(self::getFilePathByUri($uri));
    }

    public static function readStaticFile(string $uri): void
    {
        readfile(self::getFilePathByUri($uri));
    }

    public static function storeFile(string $uri, string $content): void
    {
        $filePath = self::getFilePathByUri($uri);
        file_put_contents($filePath, $content);
    }

    public static function setCacheDir(string $cacheDir)
    {
        self::$cacheDir = $cacheDir;
    }
}
