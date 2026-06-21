<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('property_image_uploads')
            ->whereIn('status', ['staged', 'stored', 'attached'])
            ->update(['status' => 'uploaded']);

        DB::table('property_image_uploads')
            ->whereIn('status', ['optimizing', 'processing'])
            ->update(['status' => 'processing']);

        DB::table('property_image_uploads')
            ->where('status', 'completed')
            ->update(['status' => 'ready']);

        DB::table('property_photos')
            ->whereIn('processing_status', ['pending', 'queued'])
            ->update(['processing_status' => 'uploaded']);

        DB::table('property_photos')
            ->whereIn('processing_status', ['optimizing', 'processing'])
            ->update(['processing_status' => 'processing']);

        DB::table('property_photos')
            ->where('processing_status', 'completed')
            ->update(['processing_status' => 'ready']);
    }

    public function down(): void
    {
        DB::table('property_image_uploads')
            ->where('status', 'uploaded')
            ->update(['status' => 'stored']);

        DB::table('property_image_uploads')
            ->where('status', 'ready')
            ->update(['status' => 'completed']);

        DB::table('property_photos')
            ->where('processing_status', 'uploaded')
            ->update(['processing_status' => 'queued']);

        DB::table('property_photos')
            ->where('processing_status', 'ready')
            ->update(['processing_status' => 'completed']);
    }
};
