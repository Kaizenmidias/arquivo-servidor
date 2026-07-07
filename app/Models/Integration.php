<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\IntegrationCacheService;

class Integration extends Model
{
    public const KEY_GTM = 'gtm';
    public const KEY_META_PIXEL = 'meta_pixel';

    protected $fillable = [
        'key',
        'name',
        'external_id',
        'is_active',
        'settings_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            app(IntegrationCacheService::class)->invalidate();
        });

        static::deleted(function (): void {
            app(IntegrationCacheService::class)->invalidate();
        });
    }
}
