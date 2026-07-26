<?php

namespace Example\Directive\Renderers;

use Example\AST\DirectiveRenderer;
use Example\AST\GenericDirectiveNode;
use Example\Compiler\Compiler;

class EmptyDirectiveRenderer implements DirectiveRenderer
{
    public function render(GenericDirectiveNode $node, Compiler $compiler): string
    {
        // Se chamado como diretiva autônoma com argumentos (ex: @empty($users))
        if ($node->arguments) {
            return "<?php if(empty({$node->arguments})): ?>" . $compiler->compile($node->body) . "<?php endif; ?>";
        }

        // Quando conectado ao @for, a renderização do bloco de fallback é gerenciada por ForDirectiveRenderer
        return '';
    }
}
