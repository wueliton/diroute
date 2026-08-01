<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_d39b7ddc4340fd416ad34fd1c1026dfb extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <?php echo $componentRenderer->render('main-layout', [], function() use ($context) {extract($context, EXTR_SKIP); ?>
<h1><?php echo $id; ?></h1>
<?php }); ?>
    <?php }
}