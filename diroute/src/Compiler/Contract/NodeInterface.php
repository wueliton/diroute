<?php

namespace Diroute\Compiler\Contract;

interface NodeInterface
{
    /**
     * Retorna os nós filhos deste nó na AST.
     * @return NodeInterface[]
     */
    public function getChildren(): array;

    /**
     * Permite modificação/substituição de filhos (útil nas Passes de Otimização).
     * @param NodeInterface[] $children
     */
    public function setChildren(array $children): void;
}
