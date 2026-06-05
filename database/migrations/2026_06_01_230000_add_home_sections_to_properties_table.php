<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('show_in_home_selecao_especial')->default(false)->after('destaque');
            $table->boolean('show_in_home_mais_procurados')->default(false)->after('show_in_home_selecao_especial');
            $table->boolean('show_in_home_visto_recentemente')->default(false)->after('show_in_home_mais_procurados');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_home_selecao_especial',
                'show_in_home_mais_procurados',
                'show_in_home_visto_recentemente',
            ]);
        });
    }
};

