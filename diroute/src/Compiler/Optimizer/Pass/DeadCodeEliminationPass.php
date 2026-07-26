<?php

namespace Diroute\Compiler\Optimizer\Pass;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Optimizer\Contract\OptimizationPassInterface;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\TextNode;

class DeadCodeEliminationPass implements OptimizationPassInterface
{
    public function optimize(AST $ast): AST
    {
        $optimizedRoot = $this->optimizeChildren($ast->root);
        return new AST($optimizedRoot);
    }

    private function optimizeChildren(NodeInterface $node): NodeInterface
    {
        $children = $node->getChildren();
        $filteredChildren = [];

        foreach ($children as $child) {
            // Remove TextNodes vazios criados por substituições anteriores
            if ($child instanceof TextNode && $child->isRawHtml && $child->content === '') {
                continue;
            }

            // Eliminação estática de @if(false) ou @if(0) em tempo de compilação
            if ($child instanceof DirectiveNode && $child->name === 'if') {
                $expr = \trim((string) $child->expression);
                if ($expr === 'false' || $expr === '0') {
                    // Se houver ramificação @else, ela é promovida; caso contrário, remove o nó
                    if (!empty($child->branches)) {
                        $lastBranch = \end($child->branches);
                        if ($lastBranch->name === 'else') {
                            foreach ($lastBranch->getChildren() as $elseChild) {
                                $filteredChildren[] = $this->optimizeChildren($elseChild);
                            }
                        }
                    }
                    continue;
                }
            }

            $filteredChildren[] = $this->optimizeChildren($child);
        }

        $node->setChildren($filteredChildren);
        return $node;
    }
}
