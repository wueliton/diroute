<?php

namespace Example\Runtime;

class TemplateCache
{
    public function __construct(
        private ?string $cacheDir = null,
    ) {
        $this->cacheDir = $cacheDir ?? dirname(__DIR__, 2) . '/var/cache';
    }

    public function getCacheFile(string $template): string
    {
        $this->ensureCacheDirectory();
        $hash = sha1($template);
        return $this->cacheDir . '/' . $hash . '.php';
    }

    public function exists(string $template): bool
    {
        return is_file($this->getCacheFile($template)) && false;
    }

    public function write(string $template, string $compiledCode): string
    {
        $cacheFile = $this->getCacheFile($template);
        file_put_contents($cacheFile, $compiledCode, LOCK_EX);
        return $cacheFile;
    }

    public function read(string $template): string
    {
        $cacheFile = $this->getCacheFile($template);
        return file_get_contents($cacheFile) ?: '';
    }

    private function ensureCacheDirectory(): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
}
