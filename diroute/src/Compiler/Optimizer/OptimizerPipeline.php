<?php

namespace Diroute\Compiler\Optimizer;

use Diroute\Compiler\Optimizer\Contract\OptimizationPassInterface;
use Diroute\Compiler\Parser\AST;

class OptimizerPipeline
{
    /** @var OptimizationPassInterface[] */
    private array $passes = [];

    /**
     * @param OptimizationPassInterface[] $passes
     */
    public function __construct(array $passes = [])
    {
        foreach ($passes as $pass) {
            $this->addPass($pass);
        }
    }

    public function addPass(OptimizationPassInterface $pass): self
    {
        $this->passes[] = $pass;
        return $this;
    }

    public function process(AST $ast): AST
    {
        $currentAst = $ast;

        foreach ($this->passes as $pass) {
            $currentAst = $pass->optimize($currentAst);
        }

        return $currentAst;
    }
}
