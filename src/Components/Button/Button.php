<?php

namespace App\Components\Button;

use Diroute\Http\Attribute\Component;

#[Component(
    selector: 'app-button',
    template: 'button.template.html',
    styles: 'button.styles.css'
)]
class Button
{
    public string $color = 'black';
    public string $class = '';
    public bool $value = false;
    public ?string $variant = null;
}
