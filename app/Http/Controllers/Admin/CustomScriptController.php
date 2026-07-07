<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomScriptRequest;
use App\Http\Requests\Admin\UpdateCustomScriptRequest;
use App\Models\CustomScript;
use App\Services\IntegrationCacheService;
use App\Services\IntegrationRenderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomScriptController extends Controller
{
    public function index(Request $request, IntegrationRenderService $renderService): Response
    {
        $this->authorize('viewAny', CustomScript::class);

        $search = trim((string) $request->string('search'));

        $scripts = CustomScript::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->get()
            ->map(fn (CustomScript $script) => $this->serializeScript($script))
            ->values();

        return Inertia::render('Admin/Integrations/CustomScripts', [
            'scripts' => $scripts,
            'search' => $search,
            'locationOptions' => $renderService->customScriptLocations(),
            'scopeOptions' => $renderService->customScriptScopes(),
            'pageOptions' => $renderService->customScriptPageOptions(),
        ]);
    }

    public function create(IntegrationRenderService $renderService): Response
    {
        $this->authorize('create', CustomScript::class);

        return Inertia::render('Admin/Integrations/CustomScriptForm', [
            'mode' => 'create',
            'script' => null,
            'locationOptions' => $renderService->customScriptLocations(),
            'scopeOptions' => $renderService->customScriptScopes(),
            'pageOptions' => $renderService->customScriptPageOptions(),
        ]);
    }

    public function store(StoreCustomScriptRequest $request, IntegrationCacheService $cache, IntegrationRenderService $renderService): RedirectResponse
    {
        $this->authorize('create', CustomScript::class);

        CustomScript::query()->create($this->mapPayload($request));
        $cache->invalidate();

        return redirect()
            ->route('admin.integrations.custom-scripts.index')
            ->with('success', 'Código personalizado criado com sucesso.');
    }

    public function edit(CustomScript $customScript, IntegrationRenderService $renderService): Response
    {
        $this->authorize('update', $customScript);

        return Inertia::render('Admin/Integrations/CustomScriptForm', [
            'mode' => 'edit',
            'script' => $this->serializeScript($customScript),
            'locationOptions' => $renderService->customScriptLocations(),
            'scopeOptions' => $renderService->customScriptScopes(),
            'pageOptions' => $renderService->customScriptPageOptions(),
        ]);
    }

    public function update(CustomScript $customScript, UpdateCustomScriptRequest $request, IntegrationCacheService $cache, IntegrationRenderService $renderService): RedirectResponse
    {
        $this->authorize('update', $customScript);

        $customScript->update($this->mapPayload($request));
        $cache->invalidate();

        return redirect()
            ->route('admin.integrations.custom-scripts.edit', ['customScript' => $customScript->id])
            ->with('success', 'Código personalizado atualizado com sucesso.');
    }

    public function destroy(CustomScript $customScript, IntegrationCacheService $cache): RedirectResponse
    {
        $this->authorize('delete', $customScript);

        $customScript->delete();
        $cache->invalidate();

        return back()->with('success', 'Código personalizado excluído com sucesso.');
    }

    public function duplicate(CustomScript $customScript, IntegrationCacheService $cache): RedirectResponse
    {
        $this->authorize('duplicate', $customScript);

        $copy = $customScript->replicate();
        $copy->name = $customScript->name . ' (Cópia)';
        $copy->is_active = false;
        $copy->push();

        $cache->invalidate();

        return redirect()
            ->route('admin.integrations.custom-scripts.edit', ['customScript' => $copy->id])
            ->with('success', 'Código duplicado com sucesso.');
    }

    public function toggle(CustomScript $customScript, IntegrationCacheService $cache): RedirectResponse
    {
        $this->authorize('toggle', $customScript);

        $customScript->update(['is_active' => !$customScript->is_active]);
        $cache->invalidate();

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    private function serializeScript(CustomScript $script): array
    {
        $pageTarget = null;
        if (filled($script->page_target_type) && filled($script->page_target_value)) {
            $pageTarget = $script->page_target_type . ':' . $script->page_target_value;
        }

        return [
            'id' => $script->id,
            'name' => $script->name,
            'description' => $script->description,
            'location' => $script->location,
            'scope' => $script->scope,
            'page_target' => $pageTarget,
            'code' => $script->code,
            'is_active' => (bool) $script->is_active,
            'created_at' => optional($script->created_at)?->toDateTimeString(),
            'updated_at' => optional($script->updated_at)?->toDateTimeString(),
        ];
    }

    private function mapPayload(StoreCustomScriptRequest|UpdateCustomScriptRequest $request): array
    {
        $pageTarget = trim((string) $request->input('page_target', ''));
        $pageTargetType = null;
        $pageTargetValue = null;

        if ($request->input('scope') === CustomScript::SCOPE_PAGE && str_contains($pageTarget, ':')) {
            [$pageTargetType, $pageTargetValue] = array_pad(explode(':', $pageTarget, 2), 2, null);
        }

        return [
            'name' => $request->string('name')->trim()->toString(),
            'description' => $request->filled('description') ? $request->string('description')->trim()->toString() : null,
            'location' => $request->string('location')->trim()->toString(),
            'scope' => $request->string('scope')->trim()->toString(),
            'page_target_type' => $pageTargetType,
            'page_target_value' => $pageTargetValue,
            'code' => $request->input('code'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
