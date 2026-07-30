<?php

namespace App\Http\Middleware;

use App\Models\MenuItem;
use App\Models\SpecialCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    private const HIDDEN_MENU_URLS = [
        '/gestao-exclusiva',
        '/calculadora',
        '/avalie-seu-imovel',
        '/corretor-parceiro',
    ];

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        $viteManifest = public_path('build/manifest.json');
        if (is_file($viteManifest)) {
            return md5_file($viteManifest) ?: null;
        }

        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => [
                'user' => fn () => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'admin_enabled' => (bool) $user->admin_enabled,
                    'permissions' => $user->permissions,
                    'profile_photo_url' => !empty($user->profile_photo_path) ? url('/storage/' . ltrim($user->profile_photo_path, '/')) : null,
                ] : null,
            ],
            'menuItems' => fn () => MenuItem::query()
                ->where('is_active', true)
                ->where('url', '!=', '/off-market')
                ->whereNotIn('url', self::HIDDEN_MENU_URLS)
                ->orderBy('order')
                ->get(['id', 'label', 'icon', 'url', 'order', 'is_active']),
            'specialCategories' => fn () => {
                $query = SpecialCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name');

                if (Schema::hasTable('property_type_special_category')) {
                    $query->with('propertyTypes:id,nome_tipo,nome_subtipo');
                }

                return $query
                    ->get(['id', 'name', 'slug', 'description', 'cover_path', 'is_active', 'sort_order'])
                    ->map(fn (SpecialCategory $category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'cover_url' => $category->cover_url,
                        'property_types' => $category->relationLoaded('propertyTypes')
                            ? $category->propertyTypes->map(fn ($type) => [
                                'id' => $type->id,
                                'nome_tipo' => $type->nome_tipo,
                                'nome_subtipo' => $type->nome_subtipo,
                            ])->values()
                            : [],
                    ]);
            },
            'settings' => fn () => Setting::query()->pluck('valor', 'chave'),
            'paths' => fn () => (function () {
                $settings = Setting::query()->pluck('valor', 'chave');
                $admin = trim((string) ($settings['admin_path'] ?? 'admin'), '/');
                $login = trim((string) ($settings['login_path'] ?? 'login'), '/');
                $admin = $admin !== '' ? $admin : 'admin';
                $login = $login !== '' ? $login : 'login';

                return [
                    'admin' => '/' . $admin,
                    'login' => '/' . $login,
                ];
            })(),
        ];
    }
}
