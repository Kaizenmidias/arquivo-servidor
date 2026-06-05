<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Quem Somos', 'icon' => 'users', 'url' => '/quem-somos', 'order' => 1],
            ['label' => 'Off Market', 'icon' => 'tag', 'url' => '/off-market', 'order' => 2],
            ['label' => 'Gestão Exclusiva', 'icon' => 'key', 'url' => '/gestao-exclusiva', 'order' => 3],
            ['label' => 'Calculadora', 'icon' => 'calculator', 'url' => '/calculadora', 'order' => 4],
            ['label' => 'Avalie seu Imóvel', 'icon' => 'home', 'url' => '/avalie-seu-imovel', 'order' => 5],
            ['label' => 'Corretor Parceiro', 'icon' => 'user-tie', 'url' => '/corretor-parceiro', 'order' => 6],
            ['label' => 'Blog', 'icon' => 'newspaper', 'url' => '/blog', 'order' => 7],
            ['label' => 'Contatos', 'icon' => 'phone', 'url' => '/contato', 'order' => 8],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['url' => $item['url']],
                $item
            );
        }
    }
}
