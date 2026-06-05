<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('banner_title')->nullable()->after('conteudo');
            $table->string('banner_subtitle', 500)->nullable()->after('banner_title');
            $table->string('banner_image')->nullable()->after('banner_subtitle');
            $table->string('banner_title_color', 20)->default('#ffffff')->after('banner_image');
            $table->string('banner_subtitle_color', 20)->default('#ffffff')->after('banner_title_color');
            $table->string('banner_overlay_color', 20)->default('#0f172a')->after('banner_subtitle_color');
            $table->unsignedTinyInteger('banner_overlay_opacity')->default(70)->after('banner_overlay_color');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'banner_title',
                'banner_subtitle',
                'banner_image',
                'banner_title_color',
                'banner_subtitle_color',
                'banner_overlay_color',
                'banner_overlay_opacity',
            ]);
        });
    }
};

