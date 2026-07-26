<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_564c9311830e43ce4969eec309f83372 extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <?php echo $componentRenderer->render('main-layout', array (
), function() use ($context) {extract($context, EXTR_SKIP); ?>

  Olá 
  <span>Example 2</span>
  <?php echo $userName; ?> <?php foreach ($metrics as $metric): ?>

  <div>
    <h2><?php echo $metric['title']; ?></h2>
    <p><?php echo $metric['value']; ?></p>
  </div>
  <?php endforeach; ?>
<?php echo $title; ?>
<?php }); ?>
    <?php }
}