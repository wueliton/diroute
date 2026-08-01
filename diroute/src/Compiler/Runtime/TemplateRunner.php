<?php

namespace Diroute\Compiler\Runtime;

use Diroute\Compiler\Cache\CompiledTemplateCache;
use Diroute\CssEngine\CssCollector;
use Diroute\CssEngine\DirouteCssEngine;
use Diroute\Profiler\Profiler;

class TemplateRunner
{
    private ?ComponentSSRRenderer $componentRenderer = null;

    public function __construct(
        private CompiledTemplateCache $templateCache,
        private DirouteCssEngine $cssEngine,
        private ?Profiler $profiler = null
    ) {}

    public function setComponentRenderer(ComponentSSRRenderer $componentRenderer): void
    {
        $this->componentRenderer = $componentRenderer;
    }

    public function run(string $templatePath, array $context, bool $injectCssIntoHtml = false): string
    {
        $profiler = $this->profiler ?? new Profiler();
        $compiledFilePath = $this->templateCache->getOrCompile($templatePath);
        $className = 'DirouteTemplate_' . md5($templatePath);

        return $profiler->profile('Runtime: Template Execution', function () use ($compiledFilePath, $className, $context, $injectCssIntoHtml) {
            if (!class_exists($className, false)) {
                require_once $compiledFilePath['php_file'];
            }

            /** @var AbstractCompiledTemplate $template */
            $template = new $className();

            $context['componentRenderer'] = $this->componentRenderer;

            ob_start();
            $template->display($context);
            $html = ob_get_clean();

            if ($injectCssIntoHtml) {
                $finalCss = $this->buildPageCss();
                if ($finalCss !== '') {
                    $html = $this->injectCssIntoHtml($html, $finalCss);
                }
            }

            return $html;
        });
    }

    private function buildPageCss(): string
    {
        $classes = CssCollector::getUniqueClasses();
        if ($classes === []) {
            return '';
        }

        return $this->cssEngine->processClasses($classes);
    }

    private function injectCssIntoHtml(string $html, string $css): string
    {
        $css = trim($css);
        if ($css === '') {
            return $html;
        }

        if (preg_match('/<style[^>]*>.*?<\/style>/is', $html, $matches) === 1) {
            return preg_replace('/<style[^>]*>.*?<\/style>/is', '<style>' . $css . '</style>', $html, 1) ?? $html;
        }

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', '<style>' . $css . '</style></head>', $html);
        }

        return '<style>' . $css . '</style>' . $html;
    }
}
