<?php

namespace App\Components\Menu;

use Diroute\Http\Attribute\Component;

#[Component(
    selector: 'app-menu',
    template: 'menu.template.html'
)]
class Menu
{
    public array $menuOptions = [
        '/' => 'Home',
        '/contact' => 'Contato'
    ];
}
