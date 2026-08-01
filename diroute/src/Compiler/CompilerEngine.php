<?php

namespace Diroute\Compiler;

use Diroute\Compiler\Contract\CompilerInterface;
use Diroute\Compiler\Generator\PhpCodeGenerator;
use Diroute\Compiler\Lexer\Lexer;
use Diroute\Compiler\Optimizer\OptimizerPipeline;
use Diroute\Compiler\Optimizer\Pass\ComponentInlinerPass;
use Diroute\Compiler\Optimizer\Pass\DeadCodeEliminationPass;
use Diroute\Compiler\Optimizer\Pass\StaticTextMergerPass;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Parser;
use Diroute\Compiler\Parser\Registry\DirectiveRegistry;
use Diroute\CssEngine\Parser\ClassScanner;
use Diroute\Http\Registry\ComponentRegistry;
use Diroute\Profiler\Profiler;

class CompilerEngine implements CompilerInterface
{
    private Lexer $lexer;
    private Parser $parser;
    private ClassScanner $classScanner;
    private OptimizerPipeline $optimizer;
    private PhpCodeGenerator $generator;
    private Profiler $profiler;

    public function __construct(
        ?DirectiveRegistry $directiveRegistry = null,
        ?ComponentRegistry $componentRegistry = null,
        ?OptimizerPipeline $optimizer = null,
        ?ClassScanner $classScanner = null,
        ?Profiler $profiler = null
    ) {
        $directiveRegistry ??= new DirectiveRegistry();
        $componentRegistry ??= new ComponentRegistry();

        $this->lexer = new Lexer();
        $this->parser = new Parser($directiveRegistry, $componentRegistry);
        $this->classScanner = $classScanner ?? new ClassScanner();

        $this->optimizer = $optimizer ?? new OptimizerPipeline([
            new ComponentInlinerPass($componentRegistry),
            new DeadCodeEliminationPass(),
            new StaticTextMergerPass(),
        ]);

        $this->generator = new PhpCodeGenerator();
        $this->profiler = $profiler ?? new Profiler();
    }

    public function compile(string $source): string
    {
        $optimizedAst = $this->parseToAst($source);
        return $this->generateCodeFromAst($optimizedAst);
    }

    public function parseToAst(string $source): AST
    {
        $profiler = $this->profiler;
        $tokens = $profiler->profile('Compiler: Lexer', function () use ($source) {
            return $this->lexer->tokenize($source);
        });
        $ast = $profiler->profile('Compiler: Parser AST', function () use ($tokens) {
            return $this->parser->parse($tokens);
        });
        $optimizedAst = $profiler->profile('Compiler: Optimizer', function () use ($ast) {
            return $this->optimizer->process($ast);
        });
        $this->classScanner->collectFromAst($ast);

        return $optimizedAst;
    }

    public function generateCodeFromAst(AST $optimizedAst): string
    {
        $profiler = $this->profiler;
        return $profiler->profile('Compiler: Code Generation', function () use ($optimizedAst) {
            return $this->generator->generate($optimizedAst);
        });
    }
}
