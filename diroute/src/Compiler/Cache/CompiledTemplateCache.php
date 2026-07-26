<?php

namespace Diroute\Compiler\Cache;

use Diroute\Compiler\Contract\CompilerInterface;
use Diroute\Profiler\Profiler;

class CompiledTemplateCache
{
    public function __construct(
        private CompilerInterface $compiler,
        private string $cacheDir,
        private bool $autoReload = true, // true em Dev, false em Produção (Twig style)
        private ?Profiler $profiler = null
    ) {
        $this->cacheDir = rtrim($cacheDir, '/\\');
    }

    /**
     * Retorna o caminho do arquivo compilado pronto para execução.
     * Se não existir ou estiver desatualizado, compila e salva no disco.
     */
    public function getOrCompile(string $templatePath): string
    {
        $profiler = $this->profiler ?? new Profiler();

        return $profiler->profile('Cache: Resolution & Validation', function () use ($templatePath) {
            $className = 'DirouteTemplate_' . md5($templatePath);
            $compiledFilePath = "{$this->cacheDir}/{$className}.php";

            // 1. CHECAGEM DE VALIDADE DO CACHE
            if ($this->isCacheValid($templatePath, $compiledFilePath)) {
                return $compiledFilePath;
            }

            // 2. CACHE MISS: Lê o arquivo fonte e chama a sua CompilerInterface
            $source = file_get_contents($templatePath);
            $phpBody = $this->compiler->compile($source);

            // 3. Encapsula o PHP gerado dentro da estrutura de classe estática
            $fullPhpCode = $this->wrapInClass($className, $phpBody);

            // 4. Salva no disco de forma atômica
            $this->storeInCache($compiledFilePath, $fullPhpCode);

            return $compiledFilePath;
        });
    }

    private function isCacheValid(string $templatePath, string $compiledFilePath): bool
    {
        if (!file_exists($compiledFilePath)) {
            return false;
        }

        // Em Produção ($autoReload = false), assume o cache como válido sem I/O de disco
        if (!$this->autoReload) {
            return true;
        }

        // Em Dev, re-compila se o template original tiver sido modificado recentemente
        return filemtime($templatePath) <= filemtime($compiledFilePath);
    }

    private function wrapInClass(string $className, string $phpBody): string
    {
        return <<<PHP
<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class {$className} extends AbstractCompiledTemplate
{
    public function display(array \$context): void
    {
        extract(\$context, EXTR_SKIP); ?>
        {$phpBody}
    <?php }
}
PHP;
    }

    private function storeInCache(string $filePath, string $content): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $tmpFile = tempnam($this->cacheDir, 'tmp_');
        file_put_contents($tmpFile, $content, LOCK_EX);
        rename($tmpFile, $filePath);
    }
}
