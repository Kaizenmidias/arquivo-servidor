<?php

namespace App\Models;

use App\Services\IntegrationCacheService;
use Illuminate\Database\Eloquent\Model;

class CustomScript extends Model
{
    public const LOCATION_HEAD = 'head';
    public const LOCATION_BODY_START = 'body_start';
    public const LOCATION_BODY_END = 'body_end';

    public const SCOPE_SITEWIDE = 'sitewide';
    public const SCOPE_PAGE = 'page';

    protected $fillable = [
        'name',
        'description',
        'location',
        'scope',
        'page_target_type',
        'page_target_value',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
