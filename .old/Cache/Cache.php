<?php

namespace Example\Cache;

class Cache
{
    public function __construct(private string $cacheDir)
    {
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }

    public function getFilePath(string $fileName): string
    {
        return $this->cacheDir . $fileName;
    }

    public function persist(string $fileName, mixed $content): void
    {
        $filePath = $this->getFilePath($fileName);
        file_put_contents($filePath, $content);
    }

    public function get(string $fileName): string
    {
        $filePath = $this->getFilePath($fileName);

        if (!is_file($filePath)) {
            return '';
        }

        $content = file_get_contents($filePath);
        return $content;
    }

    public function hasCache(string $filePath): bool
    {
        return is_file($filePath);
    }
}
