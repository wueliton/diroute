<?php

namespace Example\Directive\Renderers;

use Example\AST\DirectiveRenderer;
use Example\AST\GenericDirectiveNode;
use Example\Compiler\Compiler;

class ElseIfDirectiveRenderer implements DirectiveRenderer
{
    public function render(GenericDirectiveNode $node, Compiler $compiler): string
    {
        $code = "<?php elseif({$node->arguments}): ?>" . $compiler->compile($node->body);

        if (!empty($node->connections)) {
            foreach ($node->connections as $conn) {
                $code .= $compiler->compileDirective($conn);
            }
        }

        return $code;
    }
}
