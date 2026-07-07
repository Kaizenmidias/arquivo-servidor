<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateIntegrationRequest;
use App\Models\Integration;
use App\Services\IntegrationCacheService;
use App\Services\IntegrationRenderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function index(IntegrationRenderService $renderService): Response
    {
        $this->authorize('viewAny', Integration::class);

        $defaults = $renderService->integrationDefinitions();
        $existing = Integration::query()
            ->whereIn('key', array_keys($defaults))
            ->get()
            ->keyBy('key');

        $integrations = collect($defaults)->map(function (array $definition, string $key) use ($existing) {
            $record = $existing->get($key);

            return [
                'key' => $key,
                'name' => $definition['name'],
                'external_id' => $record?->external_id ?? '',
                'is_active' => (bool) ($record?->is_active ?? false),
            ];
        })->values();

        return Inertia::render('Admin/Integrations/PixelsTags', [
            'integrations' => $integrations,
        ]);
    }

    public function update(string $provider, UpdateIntegrationRequest $request, IntegrationCacheService $cache): RedirectResponse
    {
        $this->authorize('update', Integration::class);

        $definition = match ($provider) {
            Integration::KEY_GTM => ['key' => Integration::KEY_GTM, 'name' => 'Google Tag Manager'],
            Integration::KEY_META_PIXEL => ['key' => Integration::KEY_META_PIXEL, 'name' => 'Meta Pixel'],
            default => abort(404),
        };

        Integration::query()->updateOrCreate(
            ['key' => $definition['key']],
            [
                'name' => $definition['name'],
                'external_id' => $request->string('external_id')->trim()->toString(),
                'is_active' => $request->boolean('is_active'),
            ]
        );

        $cache->invalidate();

        return back()->with('success', 'Integração salva com sucesso.');
    }
}
