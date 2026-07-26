<?php

namespace Diroute\Compiler\Contract;

use Diroute\Compiler\Generator\PHPBuffer;

interface NodeEmitterInterface
{
    /**
     * Emite o código PHP correspondente ao nó processado.
     * 
     * @param NodeInterface $node O nó da AST a ser emitido.
     * @param PHPBuffer $buffer Buffer de escrita do PHP.
     * @param callable(NodeInterface, PHPBuffer): void $traverse Callback para continuar a travessia recursiva nos nós filhos.
     */
    public function emit(NodeInterface $node, PHPBuffer $buffer, callable $traverse): void;
}
