<?php

namespace Example\Directive\Renderers;

use Example\AST\DirectiveRenderer;
use Example\AST\GenericDirectiveNode;
use Example\Compiler\Compiler;

class ElseDirectiveRenderer implements DirectiveRenderer
{
    public function render(GenericDirectiveNode $node, Compiler $compiler): string
    {
        return "<?php else: ?>" . $compiler->compile($node->body);
    }
}
