<?php

namespace Diroute\Compiler\Parser\Node;

use Diroute\Compiler\Contract\NodeInterface;

class DirectiveNode implements NodeInterface
{
    /**
     * @param string $name Nome da diretiva (ex: 'if', 'for')
     * @param string|null $expression Conteúdo dos argumentos em parênteses
     * @param string $rendererClass Classe FQCN que renderizará o nó em PHP
     * @param NodeInterface[] $children Nós dentro do bloco { ... }
     * @param DirectiveNode[] $branches Blocos encadeados válidos
     */
    public function __construct(
        public string $name,
        public ?string $expression,
        public string $rendererClass,
        private array $children = [],
        public array $branches = [],
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

    public function addBranch(DirectiveNode $branch): void
    {
        $this->branches[] = $branch;
    }
}
