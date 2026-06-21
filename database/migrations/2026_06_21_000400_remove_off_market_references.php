<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_items')) {
            DB::table('menu_items')
                ->where('url', '/off-market')
                ->delete();
        }

        if (Schema::hasTable('pages')) {
            DB::table('pages')
                ->where('slug', 'off-market')
                ->delete();
        }

        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'is_off_market')) {
            DB::table('properties')
                ->where('is_off_market', true)
                ->update(['is_off_market' => false]);
        }
    }

    public function down(): void
    {
        // Intencionalmente sem reversão: a funcionalidade Off Market foi removida do produto.
    }
};
