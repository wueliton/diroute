<?php

use Diroute\Http\Attribute\Page;

#[Page(
    title: 'Contato',
    description: 'Entre em contato conosco',
    template: 'contact.template.html',
    revalidate: 300
)]
class ContactPage {}
