<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_anuncio')->unique();
            $table->string('codigo_referencia')->nullable();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao');
            $table->foreignId('tipo_propriedade_id')->constrained('property_types')->cascadeOnDelete();
            $table->enum('operacao', ['Venda', 'Aluguel', 'Temporada']);
            $table->decimal('valor', 15, 2);
            $table->string('moeda')->default('BRL');
            $table->string('endereco');
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep', 10)->nullable();
            $table->string('id_localidade_xml')->nullable();
            $table->string('localidade_xml')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->decimal('area_util', 10, 2)->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->integer('quartos')->nullable();
            $table->integer('suites')->nullable();
            $table->integer('banheiros')->nullable();
            $table->integer('garagens')->nullable();
            $table->decimal('condominio', 15, 2)->nullable();
            $table->decimal('iptu', 15, 2)->nullable();
            $table->boolean('destaque')->default(false);
            $table->boolean('ativo')->default(true);
            $table->bigInteger('data_modificacao_xml')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
