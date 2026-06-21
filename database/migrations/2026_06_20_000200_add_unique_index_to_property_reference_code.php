<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('properties')
            ->where('codigo_referencia', '')
            ->update([
                'codigo_referencia' => null,
            ]);

        $duplicates = DB::table('properties')
            ->select('codigo_referencia')
            ->whereNotNull('codigo_referencia')
            ->groupBy('codigo_referencia')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('codigo_referencia');

        foreach ($duplicates as $codigoReferencia) {
            $ids = DB::table('properties')
                ->where('codigo_referencia', $codigoReferencia)
                ->orderBy('id')
                ->pluck('id');

            $idsToReset = $ids->slice(1)->values();

            if ($idsToReset->isNotEmpty()) {
                DB::table('properties')
                    ->whereIn('id', $idsToReset->all())
                    ->update([
                        'codigo_referencia' => null,
                    ]);
            }
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->unique('codigo_referencia');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropUnique(['codigo_referencia']);
        });
    }
};
