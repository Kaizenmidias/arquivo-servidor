<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Comprar', 'sort_order' => 1],
            ['name' => 'Alugar', 'sort_order' => 2],
            ['name' => 'Temporada', 'sort_order' => 3],
        ];

        foreach ($items as $item) {
            BusinessType::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}

