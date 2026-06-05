<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('session_id', 128)->index();
            $table->timestamps();

            $table->index(['property_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_views');
    }
};

