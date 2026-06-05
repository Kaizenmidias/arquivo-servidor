<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['titulo' => 'Home', 'slug' => 'home', 'conteudo' => '', 'meta_title' => 'Home - Minha Imobiliária', 'meta_description' => 'Página inicial do site.'],
            ['titulo' => 'Sobre Nós', 'slug' => 'sobre', 'conteudo' => '<h1>Sobre Nós</h1><p>Conheça nossa imobiliária.</p>', 'meta_title' => 'Sobre Nós - Minha Imobiliária', 'meta_description' => 'Saiba mais sobre nossa história e equipe.'],
            ['titulo' => 'Contato', 'slug' => 'contato', 'conteudo' => '<h1>Contato</h1><p>Entre em contato conosco.</p>', 'meta_title' => 'Contato - Minha Imobiliária', 'meta_description' => 'Entre em contato com nossa equipe.'],
        ];

        foreach ($items as $item) {
            $slug = $item['slug'];
            unset($item['slug']);

            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    ...$item,
                    'slug' => $slug,
                ]
            );
        }
    }
}
