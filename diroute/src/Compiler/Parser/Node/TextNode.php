<?php

namespace Diroute\Compiler\Parser\Node;

use Diroute\Compiler\Contract\NodeInterface;

class TextNode implements NodeInterface
{
    public function __construct(
        public string $content,
        public bool $isRawHtml = true, // false se for interpolação {{ }}
        public int $line = 1,
        public int $column = 1
    ) {}

    public function getChildren(): array
    {
        return [];
    }

    public function setChildren(array $children): void
    {
        // TextNodes não possuem nós filhos
    }
}
