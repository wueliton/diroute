<?php

use Diroute\Compiler\Runtime\AbstractCompiledTemplate;

class DirouteTemplate_5e81d4aa751ed64e5c6f511cfcf5e932 extends AbstractCompiledTemplate
{
    public function display(array $context): void
    {
        extract($context, EXTR_SKIP); ?>
        <nav class="px-md py-md d-block"><ul class="flex flex-row gap-lg"><?php foreach ($menuOptions as $link => $label): ?>

    <li><a href="<?= htmlspecialchars($link) ?>"><?php echo $label; ?></a></li>
    <?php endforeach; ?>
</ul>
</nav>
    <?php }
}