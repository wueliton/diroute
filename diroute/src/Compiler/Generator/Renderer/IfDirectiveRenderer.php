<?php

namespace Diroute\Compiler\Generator\Renderer;

use Diroute\Compiler\Contract\DirectiveRendererInterface;
use Diroute\Compiler\Generator\PHPBuffer;
use Diroute\Compiler\Parser\Node\DirectiveNode;

class IfDirectiveRenderer implements DirectiveRendererInterface
{
    public function render(DirectiveNode $node, PHPBuffer $buffer, callable $next): void
    {
        // 1. Abre o IF principal
        $buffer->writeLine("<?php if ({$node->expression}): ?>");
        $buffer->indent();

        // 2. Renderiza o conteúdo dentro do bloco IF
        foreach ($node->getChildren() as $child) {
            $next($child);
        }

        // 3. Renderiza cada ramificação (@elseif, @else) na mesma cadeia
        foreach ($node->branches as $branch) {
            if ($branch->name === 'elseif') {
                $buffer->outdent();
                $buffer->writeLine("<?php elseif ({$branch->expression}): ?>");
                $buffer->indent();

                foreach ($branch->getChildren() as $child) {
                    $next($child);
                }
            } elseif ($branch->name === 'else') {
                $buffer->outdent();
                $buffer->writeLine("<?php else: ?>");
                $buffer->indent();

                foreach ($branch->getChildren() as $child) {
                    $next($child);
                }
            }
        }

        // 4. Encerra a estrutura condicional com um único ENDIF
        $buffer->outdent();
        $buffer->writeLine("<?php endif; ?>");
    }
}
