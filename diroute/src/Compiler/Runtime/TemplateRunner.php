<?php

namespace Diroute\Compiler\Runtime;

use Diroute\Compiler\Cache\CompiledTemplateCache;
use Diroute\Profiler\Profiler;

class TemplateRunner
{
    private ?ComponentSSRRenderer $componentRenderer = null;

    public function __construct(
        private CompiledTemplateCache $templateCache,
        private ?Profiler $profiler = null
    ) {}

    public function setComponentRenderer(ComponentSSRRenderer $componentRenderer): void
    {
        $this->componentRenderer = $componentRenderer;
    }

    public function run(string $templatePath, array $context): string
    {
        $profiler = $this->profiler ?? new Profiler();
        // 1. Obtém o arquivo PHP compilado (lido da RAM/Disco via Cache Manager)
        $compiledFilePath = $this->templateCache->getOrCompile($templatePath);
        $className = 'DirouteTemplate_' . md5($templatePath);

        return $profiler->profile('Runtime: Template Execution', function () use ($compiledFilePath, $className, $context) {
            // 2. Inclui o arquivo (interceptado pelo OPcache em Produção)
            if (!class_exists($className, false)) {
                require_once $compiledFilePath;
            }

            /** @var AbstractCompiledTemplate $template */
            $template = new $className();

            $context['componentRenderer'] = $this->componentRenderer;

            // 3. Captura a saída do display()
            ob_start();

            $template->display($context);
            return ob_get_clean();
        });
    }
}
