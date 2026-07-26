<?php

namespace Diroute\Compiler\Parser\Node;

use Diroute\Compiler\Contract\NodeInterface;

class RootNode implements NodeInterface
{
    /** @param NodeInterface[] $children */
    public function __construct(private array $children = []) {}

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
