<?php

namespace Example\Directive\Renderers;

use Example\AST\DirectiveRenderer;
use Example\AST\GenericDirectiveNode;
use Example\Compiler\Compiler;

class ForDirectiveRenderer implements DirectiveRenderer
{
    private static int $loopCounter = 0;

    public function render(GenericDirectiveNode $node, Compiler $compiler): string
    {
        $emptyNode = null;
        if (!empty($node->connections)) {
            foreach ($node->connections as $conn) {
                if ($conn->name === 'empty') {
                    $emptyNode = $conn;
                    break;
                }
            }
        }

        if ($emptyNode !== null) {
            self::$loopCounter++;
            $varName = '$__empty_' . self::$loopCounter;

            return "<?php {$varName} = true; foreach({$node->arguments}): {$varName} = false; ?>"
                . $compiler->compile($node->body)
                . "<?php endforeach; if({$varName}): ?>"
                . $compiler->compile($emptyNode->body)
                . "<?php endif; ?>";
        }

        return "<?php foreach({$node->arguments}): ?>" . $compiler->compile($node->body) . "<?php endforeach; ?>";
    }
}
