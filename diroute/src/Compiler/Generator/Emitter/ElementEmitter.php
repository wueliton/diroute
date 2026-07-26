<?php

namespace Diroute\Compiler\Generator\Emitter;

use Diroute\Compiler\Contract\NodeEmitterInterface;
use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\ElementNode;

class ElementEmitter implements NodeEmitterInterface
{
    public function emit(NodeInterface $node, PHPBuffer $buffer, callable $traverse): void
    {
        if (!$node instanceof ElementNode) {
            return;
        }

        // Constrói atributos HTML caso existam
        $attributes = '';
        foreach ($node->attributes as $key => $val) {
            $attributes .= " {$key}=\"{$val}\"";
        }

        if ($node->isSelfClosing) {
            $buffer->write("<{$node->tagName}{$attributes} />");
            return;
        }

        $buffer->write("<{$node->tagName}{$attributes}>");

        foreach ($node->getChildren() as $child) {
            $traverse($child);
        }

        $buffer->write("</{$node->tagName}>");
    }
}
