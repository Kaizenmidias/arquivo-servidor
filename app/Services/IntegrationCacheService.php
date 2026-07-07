<?php

namespace App\Services;

use App\Models\CustomScript;
use App\Models\Integration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class IntegrationCacheService
{
    private const CACHE_PREFIX = 'site_integrations';

    public function activeIntegrations(): Collection
    {
        return Cache::rememberForever($this->cacheKey('integrations'), function () {
            return Integration::query()
                ->whereIn('key', [Integration::KEY_GTM, Integration::KEY_META_PIXEL])
                ->get()
                ->keyBy('key');
        });
    }

    public function activeCustomScripts(): Collection
    {
        return Cache::rememberForever($this->cacheKey('custom_scripts'), function () {
            return CustomScript::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        });
    }

    public function invalidate(): void
    {
        Cache::forget($this->cacheKey('integrations'));
        Cache::forget($this->cacheKey('custom_scripts'));
    }

    private function cacheKey(string $suffix): string
    {
        return self::CACHE_PREFIX . '.' . $suffix;
    }
}
