<?php

namespace Diroute\Compiler\Optimizer\Pass;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Optimizer\Contract\OptimizationPassInterface;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\ComponentNode;
use Diroute\Http\Registry\ComponentRegistry;

class ComponentInlinerPass implements OptimizationPassInterface
{
    public function __construct(
        private readonly ComponentRegistry $componentRegistry
    ) {}

    public function optimize(AST $ast): AST
    {
        $optimizedRoot = $this->processNode($ast->root);
        return new AST($optimizedRoot);
    }

    private function processNode(NodeInterface $node): NodeInterface
    {
        $children = $node->getChildren();
        $newChildren = [];

        foreach ($children as $child) {
            if ($child instanceof ComponentNode && $this->componentRegistry->has($child->selector)) {
                $compData = $this->componentRegistry->get($child->selector);

                if ($this->shouldRenderAtRuntime($compData)) {
                    $newChildren[] = $this->processNode($child);
                    continue;
                }
            }

            $newChildren[] = $this->processNode($child);
        }

        $node->setChildren($newChildren);

        return $node;
    }

    private function shouldRenderAtRuntime(array $compData): bool
    {
        return isset($compData['class']) && $compData['class'] !== null;
    }
}
