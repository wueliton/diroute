<?php

namespace Diroute\Compiler\Optimizer\Pass;

use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Optimizer\Contract\OptimizationPassInterface;
use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\TextNode;

class StaticTextMergerPass implements OptimizationPassInterface
{
    public function optimize(AST $ast): AST
    {
        $optimizedRoot = $this->optimizeNode($ast->root);
        return new AST($optimizedRoot);
    }

    private function optimizeNode(NodeInterface $node): NodeInterface
    {
        $children = $node->getChildren();

        if (empty($children)) {
            return $node;
        }

        $mergedChildren = [];
        /** @var TextNode|null $currentTextNode */
        $currentTextNode = null;

        foreach ($children as $child) {
            $optimizedChild = $this->optimizeNode($child);

            if ($optimizedChild instanceof TextNode && $optimizedChild->isRawHtml) {
                if ($currentTextNode === null) {
                    $currentTextNode = new TextNode(
                        content: $optimizedChild->content,
                        isRawHtml: true,
                        line: $optimizedChild->line,
                        column: $optimizedChild->column
                    );
                } else {
                    $currentTextNode = new TextNode(
                        content: $currentTextNode->content . $optimizedChild->content,
                        isRawHtml: true,
                        line: $currentTextNode->line,
                        column: $currentTextNode->column
                    );
                }
            } else {
                if ($currentTextNode !== null) {
                    $mergedChildren[] = $currentTextNode;
                    $currentTextNode = null;
                }
                $mergedChildren[] = $optimizedChild;
            }
        }

        if ($currentTextNode !== null) {
            $mergedChildren[] = $currentTextNode;
        }

        $node->setChildren($mergedChildren);

        if ($node instanceof DirectiveNode && !empty($node->branches)) {
            $optimizedBranches = [];
            foreach ($node->branches as $branch) {
                /** @var DirectiveNode $optimizedBranch */
                $optimizedBranch = $this->optimizeNode($branch);
                $optimizedBranches[] = $optimizedBranch;
            }
            $node->branches = $optimizedBranches;
        }

        return $node;
    }
}
