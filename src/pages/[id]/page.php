<?php

use Diroute\Http\Attribute\Page;

#[Page(
    title: 'Example',
    description: 'Exemplo de página',
    template: 'example.template.html',
)]
class ExamplePage
{
    public array $users = [];
}
