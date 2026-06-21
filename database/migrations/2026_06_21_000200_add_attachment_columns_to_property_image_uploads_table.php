<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_image_uploads', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('property_photo_id')->nullable()->after('property_id')->constrained('property_photos')->nullOnDelete();
            $table->timestamp('attached_at')->nullable()->after('processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('property_image_uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_photo_id');
            $table->dropConstrainedForeignId('property_id');
            $table->dropColumn('attached_at');
        });
    }
};
