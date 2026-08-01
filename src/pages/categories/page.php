<?php

use Diroute\Http\Attribute\Page;

#[Page(
    title: 'Categoria do produto',
    description: 'Categoria do produto',
    template: 'categories.template.html'
)]
class CategoriesPage
{
    public array $categories = [];
}
