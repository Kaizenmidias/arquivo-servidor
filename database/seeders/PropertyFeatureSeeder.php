<?php

namespace Database\Seeders;

use App\Models\PropertyFeature;
use Illuminate\Database\Seeder;

class PropertyFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nome' => 'Piscina', 'slug' => 'piscina', 'icone' => '🏊', 'nome_xml' => 'Piscina'],
            ['nome' => 'Churrasqueira', 'slug' => 'churrasqueira', 'icone' => '🔥', 'nome_xml' => 'Churrasqueira'],
            ['nome' => 'Academia', 'slug' => 'academia', 'icone' => '🏋️', 'nome_xml' => 'Academia'],
            ['nome' => 'Condomínio Fechado', 'slug' => 'condominio-fechado', 'icone' => '🔒', 'nome_xml' => 'CondomínioFechado'],
            ['nome' => 'Mobiliado', 'slug' => 'mobiliado', 'icone' => '🛋️', 'nome_xml' => 'Mobiliado'],
            ['nome' => 'Frente Mar', 'slug' => 'frente-mar', 'icone' => '🌊', 'nome_xml' => 'FrenteMar'],
            ['nome' => 'Aceita Pet', 'slug' => 'aceita-pet', 'icone' => '🐶', 'nome_xml' => 'AceitaPet'],
            ['nome' => 'Elevador', 'slug' => 'elevador', 'icone' => '🛗', 'nome_xml' => 'Elevador'],
            ['nome' => 'Segurança 24h', 'slug' => 'seguranca-24h', 'icone' => '👮', 'nome_xml' => 'Seguranca24h'],
            ['nome' => 'Jardim', 'slug' => 'jardim', 'icone' => '🌳', 'nome_xml' => 'Jardim'],
        ];

        foreach ($items as $item) {
            $slug = $item['slug'];
            unset($item['slug']);

            PropertyFeature::updateOrCreate(
                ['slug' => $slug],
                [
                    ...$item,
                    'slug' => $slug,
                ]
            );
        }
    }
}
