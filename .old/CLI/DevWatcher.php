<?php

namespace Example\CLI;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DevWatcher
{
    private array $fileHashes = [];

    public function __construct(
        private array $directoriesToWatch
    ) {}

    public function watch(int $poolIntervalMicrosends = 500000): void
    {
        echo "⚡ Diroute Dev Server rodando...\n";
        echo "👀 Monitorando alterações em: " . implode(', ', $this->directoriesToWatch) . "\n\n";

        $this->scanFiles(initial: true);

        while (true) {
            $this->scanFiles(initial: false);
            usleep($poolIntervalMicrosends);
        }
    }

    private function scanFiles(bool $initial): void
    {
        foreach ($this->directoriesToWatch as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $filePath = $file->getPathname();

                $ignoreFile = !preg_match('/\.(php|html|css)$/', $filePath);
                if ($ignoreFile) continue;

                $currentHash = filemtime($filePath);

                $canAddFile = !isset($this->fileHashes[$filePath]);

                if ($canAddFile) {
                    $this->fileHashes[$filePath] = $currentHash;
                } elseif ($this->fileHashes[$filePath] !== $currentHash) {
                    $this->fileHashes[$filePath] = $currentHash;

                    if (!$initial) {
                        $this->onFileChanged($filePath);
                    }
                }
            }
        }
    }

    private function onFileChanged(string $filePath): void
    {
        $time = date('H:i:s');
        echo "[{$time}] 🔄 Alteração detectada em: {$filePath}\n";

        //TODO: adicionar nova compilação;
    }
}
