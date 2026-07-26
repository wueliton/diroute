<?php

namespace Example\Compiler;

use Example\Cache\Cache;
use Example\Compiler\Compiler;
use Example\Directive\DirectiveRegistry;
use Example\Parser\Parser;
use Example\Parser\Tokenizer;
use Exception;

class TemplateCompiler
{
    public function __construct(private Cache $cache, private bool $isDevMode = false) {}

    public function getOrCompile(string $templatePath): string
    {
        if (!file_exists($templatePath)) {
            throw new Exception("Arquivo de template não encontrado: {$templatePath}");
        }

        $hash = substr(md5($templatePath), 0, 12);
        $cacheFileName = "/cmp_{$hash}.php";
        $filePath = $this->cache->getFilePath($cacheFileName);

        if (!$this->isDevMode && $this->cache->hasCache($cacheFileName)) {
            return $filePath;
        }

        if ($this->cache->hasCache($cacheFileName)) {
            $isUpdated = filemtime($templatePath) > filemtime($filePath);
            if (!$isUpdated) {
                return $filePath;
            }
        }

        $rawHtml = file_get_contents($templatePath);
        $compiledPhpCode = $this->compile($rawHtml);

        $this->cache->persist($cacheFileName, $compiledPhpCode);

        return $filePath;
    }

    public function compile(string $rawTemplate)
    {
        DirectiveRegistry::boot();
        $compiler = new Compiler();
        $parser = new Parser();
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize($rawTemplate);
        $nodes = $parser->parser($stream);
        $compiled = $compiler->compile($nodes);
        return $compiled;
    }
}
