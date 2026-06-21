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
            ['label' => 'Gestão Exclusiva', 'icon' => 'key', 'url' => '/gestao-exclusiva', 'order' => 2],
            ['label' => 'Calculadora', 'icon' => 'calculator', 'url' => '/calculadora', 'order' => 3],
            ['label' => 'Avalie seu Imóvel', 'icon' => 'home', 'url' => '/avalie-seu-imovel', 'order' => 4],
            ['label' => 'Corretor Parceiro', 'icon' => 'user-tie', 'url' => '/corretor-parceiro', 'order' => 5],
            ['label' => 'Blog', 'icon' => 'newspaper', 'url' => '/blog', 'order' => 6],
            ['label' => 'Contatos', 'icon' => 'phone', 'url' => '/contato', 'order' => 7],
        ];

        MenuItem::where('url', '/off-market')->delete();

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['url' => $item['url']],
                $item
            );
        }
    }
}
