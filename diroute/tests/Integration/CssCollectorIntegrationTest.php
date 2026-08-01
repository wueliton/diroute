<?php

use Diroute\Compiler\Cache\CompiledTemplateCache;
use Diroute\Compiler\CompilerEngine;
use Diroute\Compiler\Runtime\ComponentSSRRenderer;
use Diroute\Compiler\Runtime\TemplateRunner;
use Diroute\CssEngine\CssCollector;
use Diroute\CssEngine\DirouteCssEngine;
use Diroute\Http\Registry\ComponentRegistry;
use PHPUnit\Framework\TestCase;

class CssCollectorIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        CssCollector::flush();
    }

    public function testRenderedPageIncludesCssFromUsedComponents(): void
    {
        $tempDir = sys_get_temp_dir() . '/diroute-css-test-' . uniqid('', true);
        mkdir($tempDir, 0755, true);

        $templatePath = $tempDir . '/page.template.html';
        file_put_contents($templatePath, '<div class="p-md"><app-menu /></div>');

        $registry = new ComponentRegistry();
        $registry->register(\App\Components\Menu\Menu::class);

        $compiler = new CompilerEngine(componentRegistry: $registry);
        $cssEngine = new DirouteCssEngine();
        $cache = new CompiledTemplateCache(
            compiler: $compiler,
            cssEngine: $cssEngine,
            cacheDir: $tempDir . '/cache'
        );

        $runner = new TemplateRunner(templateCache: $cache, cssEngine: $cssEngine);
        $runner->setComponentRenderer(new ComponentSSRRenderer($registry, $runner));

        CssCollector::flush();
        $html = $runner->run($templatePath, ['title' => 'Test page'], injectCssIntoHtml: true);

        $this->assertStringContainsString('<style>', $html);
        $this->assertStringContainsString('px-md', $html);
        $this->assertStringContainsString('py-md', $html);
        $this->assertStringContainsString('d-block', $html);
        $this->assertStringContainsString('flex', $html);
        $this->assertStringContainsString('gap-lg', $html);
        $this->assertSame(1, substr_count($html, '<style>'));
    }
}
