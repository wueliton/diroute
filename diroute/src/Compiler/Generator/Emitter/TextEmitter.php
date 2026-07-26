<?php

namespace Diroute\Compiler\Generator\Emitter;

use Diroute\Compiler\Contract\NodeEmitterInterface;
use Diroute\Compiler\Contract\NodeInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\TextNode;

class TextEmitter implements NodeEmitterInterface
{
    public function emit(NodeInterface $node, PHPBuffer $buffer, callable $traverse): void
    {
        if (!$node instanceof TextNode) {
            return;
        }

        if ($node->isRawHtml) {
            // Escreve diretamente o texto HTML estático no buffer
            $buffer->write($node->content);
        } else {
            // Emite instrução PHP de interpolação com escape HTML
            $buffer->write("<?php echo {$node->content}; ?>");
        }
    }
}
