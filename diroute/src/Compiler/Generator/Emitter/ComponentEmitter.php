<?php

namespace Diroute\Compiler\Generator\Emitter;

use Diroute\Compiler\Contract\NodeEmitterInterface;
use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\ComponentNode;

class ComponentEmitter implements NodeEmitterInterface
{
    public function emit(NodeInterface $node, PHPBuffer $buffer, callable $traverse): void
    {
        if (!$node instanceof ComponentNode) {
            return;
        }

        $selector = \addslashes($node->selector);
        $propsExport = [];

        foreach ($node->attributes as $attr) {
            if ($attr->isBinding) {
                $propsExport[] = "'{$attr->name}' => {$attr->value}";
            } else {
                $propsExport[] = "'{$attr->name}' => '{$attr->value}'";
            }
        }

        $propsArrayString = '[' . implode(', ', $propsExport) . ']';

        // Emite a chamada para o ComponentSSRRenderer injetando a closure para o Slot/Children
        $buffer->write("<?php echo \$componentRenderer->render('{$selector}', {$propsArrayString}, function() use (\$context) {");
        $buffer->writeLine("extract(\$context, EXTR_SKIP); ?>");

        // Processa os filhos do nó (Slot)
        foreach ($node->getChildren() as $child) {
            $traverse($child, $buffer);
        }

        $buffer->write("<?php }); ?>");
    }
}
