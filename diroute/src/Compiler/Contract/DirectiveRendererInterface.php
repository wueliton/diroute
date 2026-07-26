<?php

namespace Diroute\Compiler\Contract;

use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\DirectiveNode;

interface DirectiveRendererInterface
{
    /**
     * Emite o código PHP correspondente ao nó da diretiva na AST.
     */
    public function render(DirectiveNode $node, PHPBuffer $buffer, callable $next): void;
}
