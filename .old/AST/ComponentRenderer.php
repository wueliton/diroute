<?php

namespace Example\AST;

use Example\Compiler\Compiler;

interface ComponentRenderer
{
    public function render(ComponentNode $node, Compiler $compiler);
}
