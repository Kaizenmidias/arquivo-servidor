<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'titulo',
        'slug',
        'template',
        'conteudo',
        'data',
        'banner_title',
        'banner_subtitle',
        'banner_image',
        'banner_title_color',
        'banner_subtitle_color',
        'banner_overlay_color',
        'banner_overlay_opacity',
        'meta_title',
        'meta_description',
        'ativo',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
