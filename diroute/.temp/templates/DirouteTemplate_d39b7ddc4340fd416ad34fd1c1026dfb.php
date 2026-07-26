<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_d39b7ddc4340fd416ad34fd1c1026dfb extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <?php echo $id; ?>
    <?php }
}