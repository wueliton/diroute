<?php

namespace Diroute\Compiler\Generator\Renderer;

use Diroute\Compiler\Contract\DirectiveRendererInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\DirectiveNode;

class ForDirectiveRenderer implements DirectiveRendererInterface
{
    public function render(DirectiveNode $node, PHPBuffer $buffer, callable $next): void
    {
        $expr = $node->expression ?? '';

        $emptyBranch = null;
        foreach ($node->branches as $branch) {
            if ($branch->name === 'empty') {
                $emptyBranch = $branch;
                break;
            }
        }

        if ($emptyBranch !== null) {
            $buffer->writeLine("<?php \$__empty = true; ?>");
            $buffer->writeLine("<?php foreach ({$expr}): ?>");
            $buffer->indent();

            $buffer->writeLine("<?php \$__empty = false; ?>");

            foreach ($node->getChildren() as $child) {
                $next($child);
            }

            $buffer->outdent();
            $buffer->writeLine("<?php endforeach; ?>");

            $buffer->writeLine("<?php if (\$__empty): ?>");
            $buffer->indent();

            foreach ($emptyBranch->getChildren() as $child) {
                $next($child);
            }

            $buffer->outdent();
            $buffer->writeLine("<?php endif; ?>");
        } else {
            $buffer->writeLine("<?php foreach ({$expr}): ?>");
            $buffer->indent();

            foreach ($node->getChildren() as $child) {
                $next($child);
            }

            $buffer->outdent();
            $buffer->writeLine("<?php endforeach; ?>");
        }
    }
}
