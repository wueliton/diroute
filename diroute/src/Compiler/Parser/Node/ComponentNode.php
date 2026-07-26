<?php

namespace Diroute\Compiler\Parser\Node;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Parser\Node\AttributeNode;

class ComponentNode implements NodeInterface
{
    /**
     * @param string $selector Nome do componente (ex: "app-button" ou "x-button")
     * @param AttributeNode[] $props Propriedades passadas no componente
     * @param NodeInterface[] $children Slot padrão / Conteúdo interno do componente
     */
    public function __construct(
        public string $selector,
        public array $props = [],
        private array $children = [],
        public int $line = 1,
        public int $column = 1
    ) {}

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(NodeInterface $node): void
    {
        $this->children[] = $node;
    }
}
