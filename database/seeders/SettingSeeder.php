<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['chave' => 'nome_empresa', 'valor' => 'Minha Imobiliária'],
            ['chave' => 'email_contato', 'valor' => 'contato@minhaimobiliaria.com.br'],
            ['chave' => 'telefone', 'valor' => '(11) 99999-9999'],
            ['chave' => 'whatsapp', 'valor' => '5511999999999'],
            ['chave' => 'endereco', 'valor' => 'Rua Exemplo, 123 - São Paulo/SP'],
            ['chave' => 'script_head', 'valor' => ''],
            ['chave' => 'script_body_top', 'valor' => ''],
            ['chave' => 'script_body_bottom', 'valor' => ''],
        ];

        foreach ($items as $item) {
            Setting::updateOrCreate(
                ['chave' => $item['chave']],
                ['valor' => $item['valor']]
            );
        }
    }
}
