<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_db3d1326d54cb7a6c4a6119bc48941f9 extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <!doctype html>
<html lang="en"><head><meta charset="UTF-8" />
    <meta name="viewport"  content="width=device-width, initial-scale=1.0" />
    <title><?php echo $title; ?></title>
  </head>
  <body><?php echo $componentRenderer->render('app-menu', [], function() use ($context) {extract($context, EXTR_SKIP); ?>
<?php }); ?>
    <div class="mx-auto px-md"><?php echo $slot; ?></div>
  </body>
</html>
    <?php }
}