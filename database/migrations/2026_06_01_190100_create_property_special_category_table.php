<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_special_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('special_category_id')->constrained('special_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['property_id', 'special_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_special_category');
    }
};
