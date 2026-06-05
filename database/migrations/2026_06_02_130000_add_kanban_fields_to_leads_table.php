<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('categoria')->default('leads')->after('origem');
            $table->string('status')->default('Novo Lead')->after('categoria');
            $table->timestamp('proximo_contato_em')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'status', 'proximo_contato_em']);
        });
    }
};

