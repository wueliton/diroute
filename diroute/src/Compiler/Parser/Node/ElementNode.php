<?php

namespace Diroute\Compiler\Parser\Node;

use Diroute\Compiler\Contract\NodeInterface;

class ElementNode implements NodeInterface
{
    /**
     * @param string $tagName ex: "div", "section"
     * @param array<string, string> $attributes ex: ["class" => "btn", "id" => "main"]
     * @param NodeInterface[] $children
     */
    public function __construct(
        public string $tagName,
        public array $attributes = [],
        private array $children = [],
        public bool $isSelfClosing = false,
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
