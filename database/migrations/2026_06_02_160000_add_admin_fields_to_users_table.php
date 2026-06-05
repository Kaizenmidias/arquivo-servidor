<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->nullable()->after('password');
            $table->boolean('admin_enabled')->default(true)->after('role');
            $table->json('permissions')->nullable()->after('admin_enabled');
        });

        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId) {
            DB::table('users')->where('id', $firstUserId)->update(['role' => 'admin']);
            DB::table('users')->where('id', '!=', $firstUserId)->update(['role' => 'user']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'admin_enabled', 'permissions']);
        });
    }
};

