<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_photos', function (Blueprint $table) {
            $table->unsignedBigInteger('source_size')->nullable()->after('size');
            $table->string('source_mime_type', 100)->nullable()->after('source_size');
        });
    }

    public function down(): void
    {
        Schema::table('property_photos', function (Blueprint $table) {
            $table->dropColumn([
                'source_size',
                'source_mime_type',
            ]);
        });
    }
};
