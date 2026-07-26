<?php

namespace Example\Http;

class DevReloadController
{
    public function __construct(private string $cacheDir) {}

    public function handle(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        set_time_limit(0);

        $lastCheck = $this->getLastCacheModificationTime();

        while (true) {
            clearstatcache();
            $currentCheck = $this->getLastCacheModificationTime();

            if ($currentCheck > $lastCheck) {
                echo "event:  reload\n";
                echo "data: " . json_encode(['timestamp' => $currentCheck]) . "\n\n";
                @ob_flush();
                @flush();
                break;
            }

            echo ": ping\n\n";
            @ob_flush();
            usleep(3000000);
        }

        exit;
    }

    private function getLastCacheModificationTime()
    {
        if (!is_dir($this->cacheDir)) {
            return time();
        }

        $latestTime = 0;
        $files = glob($this->cacheDir . '/*.php');

        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latestTime) {
                $latestTime = $mtime;
            }
        }

        return $latestTime;
    }
}
