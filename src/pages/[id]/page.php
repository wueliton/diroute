<?php

use Diroute\Http\Attribute\Page;

#[Page(
    title: 'Example',
    description: 'Exemplo de página',
    template: 'example.template.html',
)]
class ProductIdPage
{
    public array $users = [];
}
