<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->string('session_id', 128)->index();
            $table->timestamps();

            $table->index(['blog_post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_views');
    }
};

