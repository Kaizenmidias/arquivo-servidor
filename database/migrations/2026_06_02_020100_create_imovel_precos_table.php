<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imovel_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('properties')->cascadeOnDelete();
            $table->string('tipo')->nullable();
            $table->decimal('preco', 15, 2)->nullable();
            $table->string('moeda')->default('BRL');
            $table->string('periodicidade')->nullable();
            $table->integer('quantidade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imovel_precos');
    }
};

