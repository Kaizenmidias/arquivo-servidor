<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_type_special_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_category_id')->constrained('special_categories')->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained('property_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['special_category_id', 'property_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_type_special_category');
    }
};
