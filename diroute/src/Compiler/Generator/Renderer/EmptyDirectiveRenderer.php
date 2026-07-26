<?php

namespace Diroute\Compiler\Generator\Renderer;

use Diroute\Compiler\Contract\DirectiveRendererInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\DirectiveNode;

class EmptyDirectiveRenderer implements DirectiveRendererInterface
{
    public function render(DirectiveNode $node, PHPBuffer $buffer, callable $next): void
    {
        $buffer->outdent();
        $buffer->writeLine("<?php else: ?>");
        $buffer->indent();

        foreach ($node->getChildren() as $child) {
            $next($child);
        }
    }
}
