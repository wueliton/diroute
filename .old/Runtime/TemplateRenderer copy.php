<?php

// namespace Example\Runtime;

use Example\Compiler\Compiler;
use Example\Parser\Parser;
use Example\Parser\Tokenizer;

class TemplateRenderer
{
    private Compiler $compiler;
    private Parser $parser;
    private Tokenizer $tokenizer;

    public function __construct(
        private TemplateCache $cache,
        ?Compiler $compiler = null,
        ?Parser $parser = null,
        ?Tokenizer $tokenizer = null,
    ) {
        $this->compiler = $compiler ?? new Compiler();
        $this->parser = $parser ?? new Parser();
        $this->tokenizer = $tokenizer ?? new Tokenizer();
    }

    public function renderFile(string $filePath, array $context = []): string
    {
        $templateRaw = file_get_contents($filePath, false);
        return $this->render($templateRaw, $context);
    }

    public function render(string $template, array $context = []): string
    {
        if (!$this->cache->exists($template)) {
            $this->compileTemplate($template);
        }

        $cacheFile = $this->cache->getCacheFile($template);
        return $this->executeCachedTemplate($cacheFile, $context);
    }

    public function compileTemplate(string $template): string
    {
        $stream = $this->tokenizer->tokenize($template);
        $nodes = $this->parser->parser($stream);
        $compiled = $this->compiler->compile($nodes);
        $this->cache->write($template, $compiled);
        return $compiled;
    }

    private function executeCachedTemplate(string $cacheFile, array $context): string
    {
        extract($context, EXTR_SKIP);
        ob_start();
        include $cacheFile;
        return ob_get_clean();
    }
}
