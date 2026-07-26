<?php

namespace Diroute\Compiler\Optimizer\Contract;

use Diroute\Compiler\Parser\AST;

interface OptimizationPassInterface
{
    /**
     * Recebe uma AST, executa as transformações e retorna a AST otimizada.
     */
    public function optimize(AST $ast): AST;
}
