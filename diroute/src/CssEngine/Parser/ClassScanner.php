<?php

namespace Diroute\CssEngine\Parser;

use Diroute\Compiler\Parser\AST;
use Diroute\Compiler\Parser\Node\ComponentNode;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Parser\Node\ElementNode;
use Diroute\CssEngine\CssCollector;

class ClassScanner
{
    public function collectFromAst(AST $ast): void
    {
        $stack = $ast->root->getChildren();

        while (!empty($stack)) {
            $node = array_pop($stack);

            if ($node instanceof ElementNode || $node instanceof ComponentNode) {
                foreach ($node->attributes as $attr) {
                    if ($attr->name === 'class' && !$attr->isBinding) {
                        CssCollector::add($attr->value);
                    }
                }
            }

            if ($node instanceof DirectiveNode) {
                foreach ($node->branches as $branch) {
                    $stack[] = $branch;
                }
            }

            foreach ($node->getChildren() as $child) {
                $stack[] = $child;
            }
        }
    }
}
