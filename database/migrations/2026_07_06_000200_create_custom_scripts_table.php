<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('location');
            $table->string('scope')->default('sitewide');
            $table->string('page_target_type')->nullable();
            $table->string('page_target_value')->nullable();
            $table->longText('code');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location', 'scope', 'is_active']);
            $table->index(['page_target_type', 'page_target_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_scripts');
    }
};
