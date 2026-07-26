<?php

namespace Example\AST;

use Example\Compiler\Compiler;
use Example\AST\GenericDirectiveNode;

interface DirectiveRenderer
{
    public function render(GenericDirectiveNode $node, Compiler $compiler);
}
