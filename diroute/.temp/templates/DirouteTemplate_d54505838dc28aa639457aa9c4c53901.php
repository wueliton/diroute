<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_d54505838dc28aa639457aa9c4c53901 extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <div><button class="flex flex-col gap-sm px-sm py-xs bg-blue-100 border-none rounded-md cursor-pointer hover:bg-blue-200 active:bg-blue-300"><?php echo $slot; ?>
  </button>
</div>
    <?php }
}