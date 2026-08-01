<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_341d1d712ad4259460482862a82039d0 extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <?php echo $componentRenderer->render('main-layout', [], function() use ($context) {extract($context, EXTR_SKIP); ?>
<h1>Contact</h1>
  <div><p>Entre em contato consoco</p>
    <form class="flex flex-col gap-xs"><input type="text"  placeholder="Nome" />
      <input type="text"  placeholder="E-mail" />
      <?php echo $componentRenderer->render('app-button', [], function() use ($context) {extract($context, EXTR_SKIP); ?>
Salvar<?php }); ?>
    </form>
  </div>
<?php }); ?>
    <?php }
}