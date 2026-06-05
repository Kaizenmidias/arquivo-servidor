<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id_tipo_xml' => 1, 'nome_tipo' => 'Apartamento', 'id_subtipo_xml' => 1, 'nome_subtipo' => 'Apartamento Padrão', 'slug' => 'apartamento'],
            ['id_tipo_xml' => 2, 'nome_tipo' => 'Casa', 'id_subtipo_xml' => 2, 'nome_subtipo' => 'Casa Padrão', 'slug' => 'casa'],
            ['id_tipo_xml' => 3, 'nome_tipo' => 'Cobertura', 'id_subtipo_xml' => 3, 'nome_subtipo' => 'Cobertura', 'slug' => 'cobertura'],
            ['id_tipo_xml' => 4, 'nome_tipo' => 'Terreno', 'id_subtipo_xml' => 4, 'nome_subtipo' => 'Terreno', 'slug' => 'terreno'],
            ['id_tipo_xml' => 5, 'nome_tipo' => 'Comercial', 'id_subtipo_xml' => 5, 'nome_subtipo' => 'Sala Comercial', 'slug' => 'comercial'],
            ['id_tipo_xml' => 6, 'nome_tipo' => 'Galpão', 'id_subtipo_xml' => 6, 'nome_subtipo' => 'Galpão', 'slug' => 'galpao'],
            ['id_tipo_xml' => 7, 'nome_tipo' => 'Fazenda', 'id_subtipo_xml' => 7, 'nome_subtipo' => 'Fazenda', 'slug' => 'fazenda'],
            ['id_tipo_xml' => 8, 'nome_tipo' => 'Chácara', 'id_subtipo_xml' => 8, 'nome_subtipo' => 'Chácara', 'slug' => 'chacara'],
        ];

        foreach ($items as $item) {
            $slug = $item['slug'];
            unset($item['slug']);

            PropertyType::updateOrCreate(
                ['slug' => $slug],
                [
                    ...$item,
                    'slug' => $slug,
                ]
            );
        }
    }
}
