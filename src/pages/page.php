<?php

use Diroute\Http\Attribute\Page;

#[Page(
    title: 'Home',
    description: 'Exemplo de página',
    template: 'home.template.html',
)]
class HomePage
{
    public string $title = 'Example';
    public string $companyName = 'Diroute Inc.';
    public string $appVersion = '1.0.0';

    public string $userName = 'Pedro';
    public string $userEmail = 'pedro@diroute.dev';

    public bool $premium = true;
    public bool $showFooter = true;

    public int $notifications = 3;

    public array $metrics = [
        [
            'title' => 'Usuários',
            'value' => 1248,
            'variation' => 12,
            'color' => 'blue'
        ],
        [
            'title' => 'Pedidos',
            'value' => 854,
            'variation' => -4,
            'color' => 'green'
        ],
        [
            'title' => 'Receita',
            'value' => 98750,
            'variation' => 18,
            'color' => 'purple'
        ]
    ];

    public array $users = [
        [
            'id' => 1,
            'name' => 'Pedro',
            'email' => 'pedro@email.com',
            'city' => 'São Paulo',
            'premium' => true,
            'roles' => ['ADMIN', 'EDITOR']
        ],
        [
            'id' => 2,
            'name' => 'Maria',
            'email' => 'maria@email.com',
            'city' => 'Rio de Janeiro',
            'premium' => false,
            'roles' => ['AUTHOR']
        ],
        [
            'id' => 3,
            'name' => 'João',
            'email' => 'joao@email.com',
            'city' => 'Curitiba',
            'premium' => false,
            'roles' => []
        ]
    ];

    public array $products = [
        [
            'name' => 'Notebook',
            'price' => 5200,
            'stock' => 12,
            'discount' => 10
        ],
        [
            'name' => 'Monitor',
            'price' => 1700,
            'stock' => 0,
            'discount' => 0
        ],
        [
            'name' => 'Mouse',
            'price' => 180,
            'stock' => 48,
            'discount' => 5
        ]
    ];

    public array $footerLinks = [
        [
            'label' => 'Home',
            'url' => '/'
        ],
        [
            'label' => 'Contato',
            'url' => '/contact'
        ],
        [
            'label' => 'Documentação',
            'url' => '/docs'
        ]
    ];

    public function formatMoney(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public function formatVariation(int $value): string
    {
        return $value > 0 ? '+' . $value : (string) $value;
    }

    public function badge(string $role): string
    {
        return strtoupper($role);
    }
}
