<?php

namespace Diroute\Compiler\Generator\Emitter;

use Diroute\Compiler\Contract\NodeEmitterInterface;
use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\DirectiveNode;
use Diroute\Compiler\Contract\DirectiveRendererInterface;
use RuntimeException;

class DirectiveEmitter implements NodeEmitterInterface
{
    public function emit(NodeInterface $node, PHPBuffer $buffer, callable $traverse): void
    {
        if (!$node instanceof DirectiveNode) {
            return;
        }

        $rendererClass = $node->rendererClass;

        if (!\class_exists($rendererClass)) {
            throw new RuntimeException("Renderer Class '{$rendererClass}' para a diretiva '@{$node->name}' não encontrada.");
        }

        /** @var DirectiveRendererInterface $renderer */
        $renderer = new $rendererClass();
        $renderer->render($node, $buffer, $traverse);
    }
}
