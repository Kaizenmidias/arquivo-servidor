<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('aceita_permuta')->default(false)->after('aceita_temporada');
            $table->integer('lavabos')->nullable()->after('banheiros');
            $table->integer('andar')->nullable()->after('garagens');
            $table->decimal('area_construida', 12, 2)->nullable()->after('area_total');
            $table->decimal('valor_condominio', 12, 2)->nullable()->after('condominio');
            $table->decimal('valor_iptu', 12, 2)->nullable()->after('iptu');
            $table->boolean('mobiliado')->default(false)->after('valor_iptu');
            $table->boolean('aceita_financiamento')->default(false)->after('mobiliado');
            $table->integer('ano_construcao')->nullable()->after('aceita_financiamento');
            $table->string('posicao_solar')->nullable()->after('ano_construcao');
        });

        DB::table('properties')
            ->whereNull('area_construida')
            ->whereNotNull('area_util')
            ->update([
                'area_construida' => DB::raw('area_util'),
            ]);

        DB::table('properties')
            ->whereNull('valor_condominio')
            ->whereNotNull('condominio')
            ->update([
                'valor_condominio' => DB::raw('condominio'),
            ]);

        DB::table('properties')
            ->whereNull('valor_iptu')
            ->whereNotNull('iptu')
            ->update([
                'valor_iptu' => DB::raw('iptu'),
            ]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'aceita_permuta',
                'lavabos',
                'andar',
                'area_construida',
                'valor_condominio',
                'valor_iptu',
                'mobiliado',
                'aceita_financiamento',
                'ano_construcao',
                'posicao_solar',
            ]);
        });
    }
};
