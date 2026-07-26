<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Components\Button\Button;
use App\Components\MainLayout\MainLayout;
use Diroute\Application;
use Diroute\Profiler\Profiler;

$profiler = new Profiler();
$app = new Application(dirname(__DIR__, 1) . "/src/pages", $profiler)
    ->registerComponents([Button::class, MainLayout::class])
    ->handle($_SERVER['REQUEST_URI']);

$app->send();
echo $profiler->renderHtmlSummary();
