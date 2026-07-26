<?php

namespace App\Components\MainLayout;

use Diroute\Http\Attribute\Component;

#[Component(
    selector: 'main-layout',
    template: 'main-layout.template.html'
)]
class MainLayout
{
    public string $title = 'Diroute Example';
}
