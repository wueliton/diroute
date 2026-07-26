<?php

namespace Diroute\Compiler;

use Diroute\Compiler\Contract\CompilerInterface;
use Diroute\Compiler\Generator\PhpCodeGenerator;
use Diroute\Compiler\Lexer\Lexer;
use Diroute\Compiler\Optimizer\OptimizerPipeline;
use Diroute\Compiler\Optimizer\Pass\ComponentInlinerPass;
use Diroute\Compiler\Optimizer\Pass\DeadCodeEliminationPass;
use Diroute\Compiler\Optimizer\Pass\StaticTextMergerPass;
use Diroute\Compiler\Parser\Parser;
use Diroute\Compiler\Parser\Registry\DirectiveRegistry;
use Diroute\Http\Registry\ComponentRegistry;
use Diroute\Profiler\Profiler;

class CompilerEngine implements CompilerInterface
{
    private Lexer $lexer;
    private Parser $parser;
    private OptimizerPipeline $optimizer;
    private PhpCodeGenerator $generator;

    public function __construct(
        ?DirectiveRegistry $directiveRegistry = null,
        ?ComponentRegistry $componentRegistry = null,
        ?OptimizerPipeline $optimizer = null,
        private ?Profiler $profiler = null
    ) {
        $directiveRegistry ??= new DirectiveRegistry();
        $componentRegistry ??= new ComponentRegistry();

        $this->lexer = new Lexer($componentRegistry);
        $this->parser = new Parser($directiveRegistry);

        $this->optimizer = $optimizer ?? new OptimizerPipeline([
            new ComponentInlinerPass($componentRegistry),
            new DeadCodeEliminationPass(),
            new StaticTextMergerPass(),
        ]);

        $this->generator = new PhpCodeGenerator();
    }

    public function compile(string $source): string
    {
        $profiler = $this->profiler ?? new Profiler();
        $tokens = $profiler->profile('Compiler: Lexer', function () use ($source) {
            return $this->lexer->tokenize($source);
        });
        $ast = $profiler->profile('Compiler: Parser AST', function () use ($tokens) {
            return $this->parser->parse($tokens);
        });
        $optimizedAst = $profiler->profile('Compiler: Optimizer', function () use ($ast) {
            return $this->optimizer->process($ast);
        });

        return $profiler->profile('Compiler: Code Generation', function () use ($optimizedAst) {
            return $this->generator->generate($optimizedAst);
        });
    }
}
