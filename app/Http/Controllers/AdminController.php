<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\BusinessType;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\PropertyType;
use App\Models\SpecialCategory;
use App\Models\Lead;
use App\Models\MenuItem;
use App\Models\Setting;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Redirect::route('admin.dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($validated['remember'] ?? false);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $remember)) {
            return Redirect::back()
                ->withErrors(['email' => 'Email ou senha inválidos.'])
                ->withInput(['email' => $validated['email']]);
        }

        $user = $request->user();
        if ($user && !$user->admin_enabled) {
            Auth::logout();
            return Redirect::back()
                ->withErrors(['email' => 'Seu usuário não tem acesso ao painel.'])
                ->withInput(['email' => $validated['email']]);
        }

        $request->session()->regenerate();

        return Redirect::intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login');
    }

    public function profile(): Response
    {
        $user = User::query()->find(Auth::id());

        return Inertia::render('Admin/Profile', [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => !empty($user->profile_photo_path) ? url('/storage/' . ltrim($user->profile_photo_path, '/')) : null,
            ] : null,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::query()->find(Auth::id());
        if (!$user) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($validated['password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return Redirect::back()->withErrors([
                    'current_password' => 'Senha atual inválida.',
                ]);
            }
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = Storage::disk('public')->putFile("profiles/{$user->id}", $file);
            $payload['profile_photo_path'] = $path;
        }

        $user->update($payload);

        return Redirect::route('admin.profile');
    }

    public function index(): Response
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth()->subMonths(11);

        $monthLabels = [];
        $monthKeys = [];
        $monthCursor = $monthStart->copy();
        for ($i = 0; $i < 12; $i++) {
            $monthKeys[] = $monthCursor->format('Y-m');
            $monthLabels[] = $this->formatMonthLabelPtBr($monthCursor);
            $monthCursor->addMonth();
        }

        $startRaw = trim((string) request('start', ''));
        $endRaw = trim((string) request('end', ''));

        try {
            $rangeStart = $startRaw !== ''
                ? Carbon::parse($startRaw)->startOfDay()
                : $now->copy()->startOfMonth()->startOfDay();
        } catch (\Throwable) {
            $rangeStart = $now->copy()->startOfMonth()->startOfDay();
        }

        try {
            $rangeEnd = $endRaw !== ''
                ? Carbon::parse($endRaw)->endOfDay()
                : $now->copy()->endOfMonth()->endOfDay();
        } catch (\Throwable) {
            $rangeEnd = $now->copy()->endOfMonth()->endOfDay();
        }

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $rangeDays = max(1, $rangeStart->diffInDays($rangeEnd) + 1);
        $prevEnd = $rangeStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($rangeDays - 1)->startOfDay();

        $hasExclusive = Schema::hasColumn('properties', 'is_exclusive');
        $hasOffMarket = Schema::hasColumn('properties', 'is_off_market');
        $hasMetaTitle = Schema::hasColumn('properties', 'meta_title');
        $hasMetaDescription = Schema::hasColumn('properties', 'meta_description');

        $propertiesActive = Property::query()->where('ativo', true)->count();
        $propertiesFeatured = Property::query()->where('ativo', true)->where('destaque', true)->count();

        $propertiesActiveNewInRange = Property::query()
            ->where('ativo', true)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->count();
        $propertiesActiveNewPrev = Property::query()
            ->where('ativo', true)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $propertiesFeaturedNewInRange = Property::query()
            ->where('ativo', true)
            ->where('destaque', true)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->count();
        $propertiesFeaturedNewPrev = Property::query()
            ->where('ativo', true)
            ->where('destaque', true)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $leadsTotal = Lead::query()->count();
        $leadsToday = Lead::query()->whereDate('created_at', $now->toDateString())->count();
        $leadsInRange = Lead::query()->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $leadsPrev = Lead::query()->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $contactsTotal = Lead::query()->where('origem', 'Site - Contato')->count();
        $contactsInRange = Lead::query()->where('origem', 'Site - Contato')->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $contactsPrev = Lead::query()->where('origem', 'Site - Contato')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $propertyViewsTotal = $this->safeCountTable('property_views');
        $propertyViewsToday = $this->safeCountTableToday('property_views');
        $propertyViewsInRange = $this->safeCountTableBetween('property_views', $rangeStart, $rangeEnd);
        $propertyViewsPrev = $this->safeCountTableBetween('property_views', $prevStart, $prevEnd);

        $kpis = [
            'properties_active' => $propertiesActive,
            'properties_active_delta' => $this->percentDelta($propertiesActiveNewInRange, $propertiesActiveNewPrev),
            'properties_featured' => $propertiesFeatured,
            'properties_featured_delta' => $this->percentDelta($propertiesFeaturedNewInRange, $propertiesFeaturedNewPrev),
            'leads_total' => $leadsTotal,
            'leads_total_delta' => $this->percentDelta($leadsInRange, $leadsPrev),
            'leads_today' => $leadsToday,
            'property_views_total' => $propertyViewsTotal,
            'property_views_total_delta' => $this->percentDelta($propertyViewsInRange, $propertyViewsPrev),
            'contacts_total' => $contactsTotal,
            'contacts_total_delta' => $this->percentDelta($contactsInRange, $contactsPrev),
        ];

        $propertyStatus = [
            'sale' => Property::query()->where('ativo', true)->where('operacao', 'Venda')->count(),
            'rent' => Property::query()->where('ativo', true)->where('operacao', 'Aluguel')->count(),
            'season' => Property::query()->where('ativo', true)->where('operacao', 'Temporada')->count(),
            'exclusive' => $hasExclusive ? Property::query()->where('ativo', true)->where('is_exclusive', true)->count() : 0,
            'off_market' => $hasOffMarket ? Property::query()->where('ativo', true)->where('is_off_market', true)->count() : 0,
            'inactive' => Property::query()->where('ativo', false)->count(),
            'sale_delta' => $this->percentDelta(
                Property::query()->where('ativo', true)->where('operacao', 'Venda')->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('operacao', 'Venda')->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ),
            'rent_delta' => $this->percentDelta(
                Property::query()->where('ativo', true)->where('operacao', 'Aluguel')->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('operacao', 'Aluguel')->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ),
            'season_delta' => $this->percentDelta(
                Property::query()->where('ativo', true)->where('operacao', 'Temporada')->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('operacao', 'Temporada')->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ),
            'exclusive_delta' => $hasExclusive ? $this->percentDelta(
                Property::query()->where('ativo', true)->where('is_exclusive', true)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('is_exclusive', true)->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ) : null,
            'off_market_delta' => $hasOffMarket ? $this->percentDelta(
                Property::query()->where('ativo', true)->where('is_off_market', true)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('is_off_market', true)->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ) : null,
            'inactive_delta' => $this->percentDelta(
                Property::query()->where('ativo', false)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', false)->whereBetween('created_at', [$prevStart, $prevEnd])->count()
            ),
        ];

        $ymExpr = $this->yearMonthExpression('created_at');
        $leadsByMonth = Lead::query()
            ->selectRaw($ymExpr . " as ym, COUNT(*) as c")
            ->where('created_at', '>=', $monthStart)
            ->groupByRaw($ymExpr)
            ->pluck('c', 'ym');

        $viewsByMonth = $this->safeCountsByMonth('property_views', $monthStart);

        $trend = [
            'labels' => $monthLabels,
            'leads' => array_map(fn ($k) => (int) ($leadsByMonth[$k] ?? 0), $monthKeys),
            'views' => array_map(fn ($k) => (int) ($viewsByMonth[$k] ?? 0), $monthKeys),
        ];

        $leadOriginItems = [
            ['key' => 'property_form', 'label' => 'Formulário do imóvel', 'count' => Lead::query()->where('origem', 'Site - Interesse no Imóvel')->count()],
            ['key' => 'contact', 'label' => 'Página de contato', 'count' => Lead::query()->where('origem', 'Site - Contato')->count()],
            ['key' => 'evaluate', 'label' => 'Avaliação de imóvel', 'count' => Lead::query()->where('origem', 'Site - Avalie seu Imóvel')->count()],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'count' => Lead::query()->where('origem', 'like', '%WhatsApp%')->count()],
            ['key' => 'partner_agent', 'label' => 'Corretor parceiro', 'count' => Lead::query()->where('origem', 'Site - Corretor Parceiro')->count()],
            ['key' => 'off_market', 'label' => 'Off Market', 'count' => Lead::query()->where('origem', 'Site - Off Market')->count()],
        ];

        $seo = [
            'missing_meta_title' => $hasMetaTitle ? Property::query()
                ->where('ativo', true)
                ->where(fn ($q) => $q->whereNull('meta_title')->orWhere('meta_title', ''))
                ->count() : 0,
            'missing_meta_description' => $hasMetaDescription ? Property::query()
                ->where('ativo', true)
                ->where(fn ($q) => $q->whereNull('meta_description')->orWhere('meta_description', ''))
                ->count() : 0,
            'missing_images' => Property::query()->where('ativo', true)->doesntHave('photos')->count(),
            'missing_location' => Property::query()
                ->where('ativo', true)
                ->where(function ($q) {
                    $q
                        ->whereNull('endereco')->orWhere('endereco', '')
                        ->orWhereNull('bairro')->orWhere('bairro', '')
                        ->orWhereNull('cidade')->orWhere('cidade', '')
                        ->orWhereNull('estado')->orWhere('estado', '');
                })
                ->count(),
            'missing_slug_optimized' => $this->countUnoptimizedPropertySlugs(),
        ];

        $topProperties = $this->topViewedProperties();
        $recentLeads = $this->recentLeads();
        $recentProperties = $this->recentProperties();
        $activities = $this->recentActivities();
        $blogStats = $this->blogStats();
        $integrations = $this->integrationStatus();
        $alerts = $this->buildAlerts($seo);

        return Inertia::render('Admin/Dashboard', [
            'range' => [
                'start' => $rangeStart->toDateString(),
                'end' => $rangeEnd->toDateString(),
            ],
            'kpis' => $kpis,
            'propertyStatus' => $propertyStatus,
            'trend' => $trend,
            'leadOrigins' => $leadOriginItems,
            'seo' => $seo,
            'topProperties' => $topProperties,
            'recentLeads' => $recentLeads,
            'recentProperties' => $recentProperties,
            'activities' => $activities,
            'blogStats' => $blogStats,
            'integrations' => $integrations,
            'alerts' => $alerts,
        ]);
    }

    private function safeCountTable(string $table): int
    {
        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeCountTableToday(string $table): int
    {
        try {
            return (int) DB::table($table)->whereDate('created_at', now()->toDateString())->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeCountTableBetween(string $table, Carbon $from, Carbon $to): int
    {
        try {
            return (int) DB::table($table)->whereBetween('created_at', [$from, $to])->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function percentDelta(int|float $current, int|float $previous): ?float
    {
        $prev = (float) $previous;
        $cur = (float) $current;

        if ($prev <= 0.0) {
            return null;
        }

        return (($cur - $prev) / $prev) * 100.0;
    }

    private function safeCountsByMonth(string $table, Carbon $from): array
    {
        try {
            $ymExpr = $this->yearMonthExpression('created_at');
            return DB::table($table)
                ->selectRaw($ymExpr . " as ym, COUNT(*) as c")
                ->where('created_at', '>=', $from)
                ->groupByRaw($ymExpr)
                ->pluck('c', 'ym')
                ->map(fn ($v) => (int) $v)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function yearMonthExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    private function formatMonthLabelPtBr(Carbon $date): string
    {
        $map = [
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
        ];

        $m = (int) $date->month;
        return ($map[$m] ?? $date->format('M')) . '/' . $date->format('y');
    }

    private function countUnoptimizedPropertySlugs(): int
    {
        $items = Property::query()
            ->where('ativo', true)
            ->get(['id', 'slug']);

        return $items
            ->filter(function (Property $p) {
                $slug = trim((string) $p->slug);
                if ($slug === '') return true;
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) return true;
                if (str_contains($slug, '--')) return true;
                if (str_starts_with($slug, '-') || str_ends_with($slug, '-')) return true;
                return false;
            })
            ->count();
    }

    private function topViewedProperties(): array
    {
        try {
            $viewsSub = DB::table('property_views')
                ->select('property_id', DB::raw('COUNT(*) as views_count'))
                ->groupBy('property_id');

            $items = Property::query()
                ->where('ativo', true)
                ->with(['photos'])
                ->withCount(['leads'])
                ->leftJoinSub($viewsSub, 'pv', fn ($join) => $join->on('properties.id', '=', 'pv.property_id'))
                ->orderByDesc(DB::raw('COALESCE(pv.views_count, 0)'))
                ->limit(10)
                ->get([
                    'properties.id',
                    'properties.titulo',
                    'properties.cidade',
                    'properties.estado',
                    DB::raw('COALESCE(pv.views_count, 0) as views_count'),
                ]);
        } catch (\Throwable) {
            $items = Property::query()
                ->where('ativo', true)
                ->with(['photos'])
                ->withCount(['leads'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get([
                    'id',
                    'titulo',
                    'cidade',
                    'estado',
                ])
                ->map(function (Property $p) {
                    $p->views_count = 0;
                    return $p;
                });
        }

        return $items
            ->map(function (Property $p) {
                $photos = $p->relationLoaded('photos') ? $p->photos->sortBy('ordem') : collect();
                $photo = $photos->firstWhere('principal', true) ?? $photos->first();
                $photoUrl = $photo?->url ?: null;

                return [
                    'id' => $p->id,
                    'title' => $p->titulo,
                    'city' => trim(($p->cidade ?? '') . '/' . ($p->estado ?? '')),
                    'views' => (int) ($p->views_count ?? 0),
                    'leads' => (int) ($p->leads_count ?? 0),
                    'photo_url' => $photoUrl,
                ];
            })
            ->values()
            ->all();
    }

    private function recentLeads(): array
    {
        return Lead::query()
            ->with(['property:id,titulo'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'property_id', 'nome', 'telefone', 'status', 'created_at'])
            ->map(fn (Lead $l) => [
                'id' => $l->id,
                'name' => $l->nome,
                'phone' => $l->telefone,
                'property' => $l->property ? $l->property->titulo : null,
                'created_at' => $l->created_at?->toISOString(),
                'status' => $l->status,
            ])
            ->values()
            ->all();
    }

    private function recentProperties(): array
    {
        return Property::query()
            ->with(['photos', 'businessType:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'titulo', 'cidade', 'estado', 'business_type_id', 'created_at'])
            ->map(function (Property $p) {
                $photos = $p->relationLoaded('photos') ? $p->photos->sortBy('ordem') : collect();
                $photo = $photos->firstWhere('principal', true) ?? $photos->first();
                $photoUrl = $photo?->url ?: null;

                return [
                    'id' => $p->id,
                    'title' => $p->titulo,
                    'city' => trim(($p->cidade ?? '') . '/' . ($p->estado ?? '')),
                    'type' => $p->businessType?->name ?? null,
                    'created_at' => $p->created_at?->toISOString(),
                    'photo_url' => $photoUrl,
                ];
            })
            ->values()
            ->all();
    }

    private function recentActivities(): array
    {
        $items = [];

        $leads = Lead::query()->orderByDesc('created_at')->limit(6)->get(['id', 'nome', 'origem', 'created_at']);
        foreach ($leads as $l) {
            $items[] = [
                'type' => 'lead',
                'title' => 'Lead recebido',
                'description' => trim(($l->nome ?? '') . ' • ' . ($l->origem ?? '')),
                'at' => $l->created_at?->toISOString(),
            ];
        }

        $propertiesNew = Property::query()->orderByDesc('created_at')->limit(6)->get(['id', 'titulo', 'created_at']);
        foreach ($propertiesNew as $p) {
            $items[] = [
                'type' => 'property',
                'title' => 'Novo imóvel cadastrado',
                'description' => $p->titulo,
                'at' => $p->created_at?->toISOString(),
            ];
        }

        $propertiesUpdated = Property::query()
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get(['id', 'titulo', 'created_at', 'updated_at']);
        foreach ($propertiesUpdated as $p) {
            if ($p->updated_at && $p->created_at && $p->updated_at->diffInMinutes($p->created_at) < 2) {
                continue;
            }
            $items[] = [
                'type' => 'property',
                'title' => 'Imóvel atualizado',
                'description' => $p->titulo,
                'at' => $p->updated_at?->toISOString(),
            ];
        }

        $pages = Page::query()->where('ativo', true)->orderByDesc('updated_at')->limit(4)->get(['id', 'titulo', 'updated_at']);
        foreach ($pages as $p) {
            $items[] = [
                'type' => 'page',
                'title' => 'Página publicada/atualizada',
                'description' => $p->titulo,
                'at' => $p->updated_at?->toISOString(),
            ];
        }

        $posts = BlogPost::query()->whereNotNull('published_at')->orderByDesc('published_at')->limit(4)->get(['id', 'title', 'published_at']);
        foreach ($posts as $p) {
            $items[] = [
                'type' => 'blog',
                'title' => 'Artigo publicado',
                'description' => $p->title,
                'at' => $p->published_at?->toISOString(),
            ];
        }

        return collect($items)
            ->filter(fn ($i) => !empty($i['at']))
            ->sortByDesc('at')
            ->take(12)
            ->values()
            ->all();
    }

    private function blogStats(): ?array
    {
        $total = BlogPost::query()->count();
        if ($total === 0) {
            return null;
        }

        $viewsTotal = $this->safeCountTable('blog_post_views');

        $top = null;
        try {
            $row = DB::table('blog_post_views')
                ->select('blog_post_id', DB::raw('COUNT(*) as c'))
                ->groupBy('blog_post_id')
                ->orderByDesc('c')
                ->first();

            if ($row && !empty($row->blog_post_id)) {
                $post = BlogPost::query()->find($row->blog_post_id);
                if ($post) {
                    $top = [
                        'id' => $post->id,
                        'title' => $post->title,
                        'views' => (int) ($row->c ?? 0),
                    ];
                }
            }
        } catch (\Throwable) {
            $top = null;
        }

        return [
            'total_posts' => $total,
            'total_views' => $viewsTotal,
            'top_post' => $top,
        ];
    }

    private function integrationStatus(): array
    {
        $scripts = Setting::query()
            ->whereIn('chave', ['script_head', 'script_body_top', 'script_body_bottom'])
            ->pluck('valor', 'chave');

        $blob = implode("\n", $scripts->all());

        $ga = str_contains($blob, 'googletagmanager.com/gtag/js') || preg_match('/G-[A-Z0-9]{6,}/', $blob);
        $gtm = str_contains($blob, 'googletagmanager.com/gtm.js') || preg_match('/GTM-[A-Z0-9]+/', $blob);
        $meta = str_contains($blob, 'connect.facebook.net') || str_contains($blob, 'fbq(');
        $clarity = str_contains($blob, 'clarity.ms') || str_contains($blob, 'clarity(');

        return [
            'google_analytics' => (bool) $ga,
            'google_tag_manager' => (bool) $gtm,
            'meta_pixel' => (bool) $meta,
            'microsoft_clarity' => (bool) $clarity,
        ];
    }

    private function buildAlerts(array $seo): array
    {
        $alerts = [];

        if (!empty($seo['missing_images'])) {
            $alerts[] = ['level' => 'warning', 'text' => 'Existem imóveis sem fotos.'];
        }
        if (!empty($seo['missing_meta_title']) || !empty($seo['missing_meta_description'])) {
            $alerts[] = ['level' => 'warning', 'text' => 'Existem imóveis sem SEO configurado (Meta Title/Description).'];
        }
        if (!empty($seo['missing_location'])) {
            $alerts[] = ['level' => 'warning', 'text' => 'Existem imóveis sem localização completa (endereço/bairro/cidade/UF).'];
        }

        $leadsNoResponse = Lead::query()
            ->where('status', 'Novo Lead')
            ->where('created_at', '<=', now()->subDay())
            ->count();
        if ($leadsNoResponse > 0) {
            $alerts[] = ['level' => 'info', 'text' => 'Existem leads sem resposta.'];
        }

        $feedLast = Setting::query()->where('chave', 'feed_imoveis_last_generated_at')->value('valor');
        $feedOutdated = true;
        try {
            if (!empty($feedLast)) {
                $dt = Carbon::parse((string) $feedLast);
                $feedOutdated = $dt->lt(now()->subDays(7));
            }
        } catch (\Throwable) {
            $feedOutdated = true;
        }
        if ($feedOutdated) {
            $alerts[] = ['level' => 'info', 'text' => 'Feed/Sitemap de imóveis pode precisar de atualização.'];
        }

        return $alerts;
    }
    
    public function properties(Request $request): Response
    {
        $propertyTypes = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get(['id', 'nome_tipo', 'nome_subtipo']);
        $selectedPropertyTypeId = $request->query('property_type_id');
        $selectedPropertyTypeId = is_null($selectedPropertyTypeId) ? null : (int) $selectedPropertyTypeId;

        $properties = Property::query()
            ->with(['propertyType', 'businessType', 'photos'])
            ->when($selectedPropertyTypeId, fn ($q) => $q->where('tipo_propriedade_id', $selectedPropertyTypeId))
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/Properties', [
            'properties' => $properties,
            'propertyTypes' => $propertyTypes,
            'selectedPropertyTypeId' => $selectedPropertyTypeId,
            'isTrash' => false,
        ]);
    }

    public function propertiesTrash(Request $request): Response
    {
        $propertyTypes = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get(['id', 'nome_tipo', 'nome_subtipo']);
        $selectedPropertyTypeId = $request->query('property_type_id');
        $selectedPropertyTypeId = is_null($selectedPropertyTypeId) ? null : (int) $selectedPropertyTypeId;

        $properties = Property::onlyTrashed()
            ->with(['propertyType', 'businessType', 'photos'])
            ->when($selectedPropertyTypeId, fn ($q) => $q->where('tipo_propriedade_id', $selectedPropertyTypeId))
            ->orderByDesc('deleted_at')
            ->get();

        return Inertia::render('Admin/Properties', [
            'properties' => $properties,
            'propertyTypes' => $propertyTypes,
            'selectedPropertyTypeId' => $selectedPropertyTypeId,
            'isTrash' => true,
        ]);
    }
    
    public function createProperty(): Response
    {
        $propertyTypes = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get();
        $businessTypes = BusinessType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $specialCategories = SpecialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $generatedReferenceCode = $this->generateUniqueCodigoReferencia();

        return Inertia::render('Admin/PropertyCreate', [
            'propertyTypes' => $propertyTypes,
            'businessTypes' => $businessTypes,
            'specialCategories' => $specialCategories,
            'generatedReferenceCode' => $generatedReferenceCode,
        ]);
    }

    public function storeProperty(Request $request)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'codigo_referencia' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'descricao' => ['required', 'string'],
            'tipo_propriedade_id' => ['required', 'integer', 'exists:property_types,id'],
            'business_type_id' => ['required', 'integer', 'exists:business_types,id'],
            'valor' => ['required', 'string'],
            'endereco' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'quartos' => ['nullable', 'integer', 'min:0'],
            'banheiros' => ['nullable', 'integer', 'min:0'],
            'garagens' => ['nullable', 'integer', 'min:0'],
            'is_exclusive' => ['nullable', 'boolean'],
            'is_off_market' => ['nullable', 'boolean'],
            'show_in_home_selecao_especial' => ['nullable', 'boolean'],
            'show_in_home_mais_procurados' => ['nullable', 'boolean'],
            'show_in_home_visto_recentemente' => ['nullable', 'boolean'],
            'featured_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'special_category_ids' => ['nullable', 'array'],
            'special_category_ids.*' => ['integer', 'exists:special_categories,id'],
        ]);

        $slug = $this->generateUniquePropertySlug($validated['titulo']);
        $codigoReferencia = $this->normalizeCodigoReferencia($validated['codigo_referencia'] ?? null);
        if ($codigoReferencia === '') {
            $codigoReferencia = $this->generateUniqueCodigoReferencia();
        }
        $codigoAnuncio = $this->generateUniqueCodigoAnuncio();
        $businessType = BusinessType::find($validated['business_type_id']);
        $valor = $this->parseBrlCurrency($validated['valor']);

        $property = Property::create([
            ...collect($validated)->except(['featured_image', 'gallery_images', 'special_category_ids'])->all(),
            'codigo_referencia' => $codigoReferencia,
            'slug' => $slug,
            'codigo_anuncio' => $codigoAnuncio,
            'moeda' => 'BRL',
            'ativo' => true,
            'valor' => $valor,
            'operacao' => $this->mapBusinessTypeNameToLegacyOperacao($businessType?->name),
        ]);

        if (!empty($validated['special_category_ids'])) {
            $property->specialCategories()->sync($validated['special_category_ids']);
        }

        $photosToCreate = [];
        $order = 1;

        if ($request->hasFile('featured_image')) {
            $featured = $request->file('featured_image');
            $path = Storage::disk('public')->putFile("properties/{$property->id}", $featured);
            $url = url('/storage/' . $path);
            $photosToCreate[] = [
                'property_id' => $property->id,
                'arquivo' => $path,
                'url' => $url,
                'principal' => true,
                'ordem' => 0,
            ];
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images', []) as $file) {
                $path = Storage::disk('public')->putFile("properties/{$property->id}", $file);
                $url = url('/storage/' . $path);
                $photosToCreate[] = [
                    'property_id' => $property->id,
                    'arquivo' => $path,
                    'url' => $url,
                    'principal' => false,
                    'ordem' => $order,
                ];
                $order++;
            }
        }

        if (!empty($photosToCreate)) {
            PropertyPhoto::insert($photosToCreate);
        }

        return Redirect::route('admin.properties.edit', ['property' => $property->id]);
    }

    public function editProperty(Property $property): Response
    {
        $property->load(['photos', 'specialCategories']);

        $propertyTypes = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get();
        $businessTypes = BusinessType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $specialCategories = SpecialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/PropertyCreate', [
            'propertyTypes' => $propertyTypes,
            'businessTypes' => $businessTypes,
            'specialCategories' => $specialCategories,
            'property' => $property,
            'selectedSpecialCategoryIds' => $property->specialCategories->pluck('id')->values(),
        ]);
    }

    public function updateProperty(Request $request, Property $property)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'codigo_referencia' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'descricao' => ['required', 'string'],
            'tipo_propriedade_id' => ['required', 'integer', 'exists:property_types,id'],
            'business_type_id' => ['required', 'integer', 'exists:business_types,id'],
            'valor' => ['required', 'string'],
            'endereco' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'quartos' => ['nullable', 'integer', 'min:0'],
            'banheiros' => ['nullable', 'integer', 'min:0'],
            'garagens' => ['nullable', 'integer', 'min:0'],
            'is_exclusive' => ['nullable', 'boolean'],
            'is_off_market' => ['nullable', 'boolean'],
            'show_in_home_selecao_especial' => ['nullable', 'boolean'],
            'show_in_home_mais_procurados' => ['nullable', 'boolean'],
            'show_in_home_visto_recentemente' => ['nullable', 'boolean'],
            'featured_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
            'photo_order_ids' => ['nullable', 'array'],
            'photo_order_ids.*' => ['integer'],
            'special_category_ids' => ['nullable', 'array'],
            'special_category_ids.*' => ['integer', 'exists:special_categories,id'],
        ]);

        $businessType = BusinessType::find($validated['business_type_id']);
        $valor = $this->parseBrlCurrency($validated['valor']);

        $codigoReferencia = $this->normalizeCodigoReferencia($validated['codigo_referencia'] ?? null);
        if ($codigoReferencia === '') {
            $codigoReferencia = $this->generateUniqueCodigoReferencia();
        }

        $property->fill([
            ...collect($validated)->except(['featured_image', 'gallery_images', 'special_category_ids', 'valor'])->all(),
            'codigo_referencia' => $codigoReferencia,
            'valor' => $valor,
            'operacao' => $this->mapBusinessTypeNameToLegacyOperacao($businessType?->name),
        ]);
        $property->save();

        $property->specialCategories()->sync($validated['special_category_ids'] ?? []);

        if ($request->hasFile('featured_image')) {
            $existingFeatured = $property->photos()->where('principal', true)->first();
            if ($existingFeatured) {
                Storage::disk('public')->delete($existingFeatured->arquivo);
                $existingFeatured->delete();
            }

            $featured = $request->file('featured_image');
            $path = Storage::disk('public')->putFile("properties/{$property->id}", $featured);
            $url = url('/storage/' . $path);
            PropertyPhoto::create([
                'property_id' => $property->id,
                'arquivo' => $path,
                'url' => $url,
                'principal' => true,
                'ordem' => 0,
            ]);
        }

        $removeIds = collect($validated['remove_photo_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        if ($removeIds->isNotEmpty()) {
            $photosToRemove = $property->photos()
                ->where('principal', false)
                ->whereIn('id', $removeIds->all())
                ->get();

            foreach ($photosToRemove as $photo) {
                Storage::disk('public')->delete($photo->arquivo);
                $photo->delete();
            }
        }

        $orderIds = collect($validated['photo_order_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        if ($orderIds->isNotEmpty()) {
            foreach ($orderIds as $idx => $photoId) {
                PropertyPhoto::query()
                    ->where('property_id', $property->id)
                    ->where('principal', false)
                    ->where('id', $photoId)
                    ->update(['ordem' => $idx + 1]);
            }
        }

        if ($request->hasFile('gallery_images')) {
            $currentMaxOrder = (int) ($property->photos()->max('ordem') ?? 0);
            $order = max(1, $currentMaxOrder + 1);

            foreach ($request->file('gallery_images', []) as $file) {
                $path = Storage::disk('public')->putFile("properties/{$property->id}", $file);
                $url = url('/storage/' . $path);
                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'arquivo' => $path,
                    'url' => $url,
                    'principal' => false,
                    'ordem' => $order,
                ]);
                $order++;
            }
        }

        return Redirect::route('admin.properties');
    }

    public function destroyProperty(Property $property)
    {
        $property->delete();

        return Redirect::route('admin.properties');
    }

    public function restoreProperty(int $property)
    {
        $model = Property::withTrashed()->findOrFail($property);
        $model->restore();

        return Redirect::route('admin.properties.trash');
    }

    public function forceDestroyProperty(int $property)
    {
        $model = Property::withTrashed()->findOrFail($property);
        $model->load(['photos', 'specialCategories', 'features']);
        $this->purgeProperty($model);

        return Redirect::route('admin.properties.trash');
    }

    public function bulkProperties(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'max:50'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $action = $validated['action'];
        $ids = collect($validated['ids'])->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
        if (!count($ids)) {
            return Redirect::back();
        }

        if (in_array($action, ['restore', 'force_delete'], true)) {
            $items = Property::withTrashed()
                ->whereIn('id', $ids)
                ->get();
        } else {
            $items = Property::query()
                ->whereIn('id', $ids)
                ->get();
        }

        if ($action === 'delete') {
            foreach ($items as $p) {
                $p->delete();
            }
        } elseif ($action === 'restore') {
            foreach ($items as $p) {
                if (method_exists($p, 'restore')) {
                    $p->restore();
                }
            }
        } elseif ($action === 'force_delete') {
            foreach ($items as $p) {
                $p->loadMissing(['photos', 'specialCategories', 'features']);
                $this->purgeProperty($p);
            }
        } elseif ($action === 'activate') {
            Property::withTrashed()->whereIn('id', $ids)->update(['ativo' => true]);
        } elseif ($action === 'deactivate') {
            Property::withTrashed()->whereIn('id', $ids)->update(['ativo' => false]);
        }

        return Redirect::back();
    }

    private function purgeProperty(Property $property): void
    {
        foreach ($property->photos as $photo) {
            if (!empty($photo->arquivo)) {
                Storage::disk('public')->delete($photo->arquivo);
            }
            $photo->delete();
        }

        $property->specialCategories()->detach();
        $property->features()->detach();
        $property->forceDelete();
    }

    public function duplicateProperty(Property $property)
    {
        $property->load(['photos', 'specialCategories', 'features']);

        $newTitle = trim($property->titulo . ' (Cópia)');
        $new = $property->replicate();
        $new->titulo = $newTitle;
        $new->slug = $this->generateUniquePropertySlug($newTitle);
        $new->codigo_referencia = $this->generateUniqueCodigoReferencia();
        $new->codigo_anuncio = $this->generateUniqueCodigoAnuncio();
        $new->ativo = false;
        $new->save();

        $new->specialCategories()->sync($property->specialCategories->pluck('id')->values()->all());
        $new->features()->sync($property->features->pluck('id')->values()->all());

        foreach ($property->photos as $photo) {
            $source = $photo->arquivo;
            $dest = null;

            if (!empty($source) && Storage::disk('public')->exists($source)) {
                $ext = pathinfo($source, PATHINFO_EXTENSION);
                $filename = Str::random(20) . ($ext ? ('.' . $ext) : '');
                $dest = "properties/{$new->id}/{$filename}";
                Storage::disk('public')->copy($source, $dest);
            }

            $path = $dest ?: $source;
            PropertyPhoto::create([
                'property_id' => $new->id,
                'arquivo' => $path,
                'url' => url('/storage/' . $path),
                'principal' => (bool) $photo->principal,
                'ordem' => (int) $photo->ordem,
            ]);
        }

        return Redirect::route('admin.properties.edit', ['property' => $new->id]);
    }

    private function parseBrlCurrency(string $input): float
    {
        $value = preg_replace('/[^\d,\.]/', '', $input);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function mapBusinessTypeNameToLegacyOperacao(?string $businessTypeName): string
    {
        if ($businessTypeName === 'Alugar') {
            return 'Aluguel';
        }

        if ($businessTypeName === 'Temporada') {
            return 'Temporada';
        }

        return 'Venda';
    }

    private function generateUniquePropertySlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Property::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function generateUniqueCodigoAnuncio(): string
    {
        do {
            $codigo = strtoupper(Str::random(8));
        } while (Property::where('codigo_anuncio', $codigo)->exists());

        return $codigo;
    }

    private function normalizeCodigoReferencia(?string $input): string
    {
        $raw = strtoupper(trim((string) ($input ?? '')));
        if ($raw === '') {
            return '';
        }

        $raw = preg_replace('/[^A-Z0-9]/', '', $raw) ?? '';
        if ($raw === '') {
            return '';
        }

        if (strlen($raw) < 8) {
            return '';
        }

        return substr($raw, 0, 8);
    }

    private function generateUniqueCodigoReferencia(): string
    {
        do {
            $codigo = strtoupper(Str::random(8));
        } while (Property::where('codigo_referencia', $codigo)->exists());

        return $codigo;
    }
    
    public function leads(): Response
    {
        $leads = Lead::query()
            ->with(['property'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/Leads', [
            'leads' => $leads,
        ]);
    }

    public function updateLead(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:60'],
            'proximo_contato_em' => ['nullable', 'date'],
        ]);

        $payload = [];
        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $payload['status'] = $validated['status'];
        }
        if (array_key_exists('proximo_contato_em', $validated)) {
            $payload['proximo_contato_em'] = $validated['proximo_contato_em'];
        }

        if (count($payload)) {
            $lead->update($payload);
        }

        return Redirect::back();
    }

    public function updateLeadsSettings(Request $request)
    {
        $validated = $request->validate([
            'kanban_columns' => ['nullable', 'array'],
            'kanban_columns.*' => ['string', 'max:60'],
            'whatsapp_template' => ['nullable', 'string', 'max:2000'],
            'email_subject_template' => ['nullable', 'string', 'max:255'],
            'email_body_template' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('kanban_columns', $validated) && is_array($validated['kanban_columns'])) {
            $columns = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $validated['kanban_columns']), fn ($v) => $v !== ''));
            Setting::updateOrCreate(
                ['chave' => 'leads_kanban_columns'],
                ['valor' => json_encode($columns)]
            );
        }

        if (array_key_exists('whatsapp_template', $validated)) {
            Setting::updateOrCreate(
                ['chave' => 'leads_whatsapp_template'],
                ['valor' => (string) ($validated['whatsapp_template'] ?? '')]
            );
        }

        if (array_key_exists('email_subject_template', $validated)) {
            Setting::updateOrCreate(
                ['chave' => 'leads_email_subject_template'],
                ['valor' => (string) ($validated['email_subject_template'] ?? '')]
            );
        }

        if (array_key_exists('email_body_template', $validated)) {
            Setting::updateOrCreate(
                ['chave' => 'leads_email_body_template'],
                ['valor' => (string) ($validated['email_body_template'] ?? '')]
            );
        }

        return Redirect::back();
    }
    
    public function appearance(): Response
    {
        $settings = Setting::all()->pluck('valor', 'chave');
        return Inertia::render('Admin/Appearance', ['settings' => $settings]);
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'button_color' => ['nullable', 'string', 'max:20'],
            'footer_bg_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:255'],
            'font_size_text' => ['nullable', 'integer', 'min:10', 'max:24'],
            'font_size_title' => ['nullable', 'integer', 'min:18', 'max:72'],
            'home_hero_overlay_color' => ['nullable', 'string', 'max:20'],
            'home_hero_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'logo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'favicon_file' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        foreach ([
            'primary_color',
            'secondary_color',
            'button_color',
            'footer_bg_color',
            'font_family',
            'font_size_text',
            'font_size_title',
            'home_hero_overlay_color',
            'home_hero_overlay_opacity',
        ] as $key) {
            if (!array_key_exists($key, $validated)) {
                continue;
            }

            Setting::updateOrCreate(
                ['chave' => $key],
                ['valor' => (string) ($validated[$key] ?? '')]
            );
        }

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = Storage::disk('public')->putFile('branding/logo', $file);
            Setting::updateOrCreate(['chave' => 'logo_url'], ['valor' => url('/storage/' . $path)]);
        }

        if ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            $path = Storage::disk('public')->putFile('branding/favicon', $file);
            Setting::updateOrCreate(['chave' => 'favicon_url'], ['valor' => url('/storage/' . $path)]);
        }

        return Redirect::route('admin.appearance');
    }
    
    public function layout(): Response
    {
        $settings = Setting::all()->pluck('valor', 'chave');
        return Inertia::render('Admin/Layout', ['settings' => $settings]);
    }
    
    public function pages(): Response
    {
        $home = Page::firstOrNew(['slug' => 'home']);
        if (!$home->exists) {
            $home->fill([
                'titulo' => 'Home',
                'template' => 'home',
                'conteudo' => '',
                'banner_title' => 'Seja bem vindo! Seu novo lar está aqui.',
                'banner_subtitle' => '',
                'banner_title_color' => '#ffffff',
                'banner_subtitle_color' => '#ffffff',
                'banner_overlay_color' => '#0f172a',
                'banner_overlay_opacity' => 70,
                'ativo' => true,
            ]);
        } else {
            $home->template = 'home';
            $home->titulo = $home->titulo ?: 'Home';
            if ($home->banner_title === null || $home->banner_title === '') {
                $home->banner_title = 'Seja bem vindo! Seu novo lar está aqui.';
            }
        }
        $home->save();

        $about = Page::where('slug', 'quem-somos')->first();
        if (!$about) {
            $legacyAbout = Page::where('slug', 'sobre')->first();
            if ($legacyAbout) {
                $legacyAbout->slug = 'quem-somos';
                $about = $legacyAbout;
            }
        }
        if (!$about) {
            $about = new Page(['slug' => 'quem-somos']);
        }

        if (!$about->exists) {
            $about->fill([
                'titulo' => 'Quem Somos',
                'template' => 'about',
                'conteudo' => '',
                'ativo' => true,
                'data' => [
                    'hero_title_primary' => 'Conexão patrimonial.',
                    'hero_title_secondary' => 'Valor que permanece.',
                    'hero_subtitle' => 'Há mais de 12 anos conectando pessoas aos melhores endereços com expertise, confiança e sofisticação.',
                    'hero_button_label' => 'Explorar imóveis',
                    'hero_button_url' => '/imoveis',
                    'stats' => [
                        ['value' => '12+', 'label' => 'Anos de mercado'],
                        ['value' => '800+', 'label' => 'Imóveis negociados'],
                        ['value' => '2.350+', 'label' => 'Parceiros de negócios'],
                        ['value' => '100%', 'label' => 'Presença em Alphaville'],
                    ],
                    'essence' => [
                        'kicker' => 'Nossa essência',
                        'title_primary' => 'Construímos relações,',
                        'title_highlight' => 'não apenas negócios',
                        'text_1' => '',
                        'text_2' => '',
                        'bullets' => ['Alto padrão', 'Alphaville e região', 'Atendimento exclusivo'],
                        'badge_value' => '12',
                        'badge_label' => 'ANOS',
                    ],
                    'team' => [
                        'kicker' => 'Nosso time',
                        'title' => 'Quem faz acontecer',
                        'subtitle' => 'Profissionais apaixonados por conectar pessoas aos melhores endereços',
                        'members' => [
                            ['name' => 'Profissional 1', 'role' => 'Cargo', 'photo' => null],
                            ['name' => 'Profissional 2', 'role' => 'Cargo', 'photo' => null],
                        ],
                    ],
                    'quote' => [
                        'text' => 'Um endereço não é apenas um lugar. É onde a vida acontece, onde memórias são criadas, onde histórias começam.',
                        'author' => '',
                        'author_role' => '',
                    ],
                    'pillars' => [
                        ['title' => 'Confiança', 'description' => 'Transparência em cada etapa da negociação, com orientação real e objetiva.'],
                        ['title' => 'Conexão', 'description' => 'Entendemos o momento de cada cliente para sugerir o imóvel certo.'],
                        ['title' => 'Expertise', 'description' => 'Conhecimento de mercado, valorização e regiões para decisões seguras.'],
                        ['title' => 'Valor', 'description' => 'Atendimento cuidadoso e foco em longo prazo, não apenas na venda.'],
                    ],
                    'territory' => [
                        'kicker' => 'Nosso território',
                        'title' => 'Alphaville é',
                        'title_highlight' => 'nossa casa',
                        'text_1' => 'Conhecemos cada rua, cada condomínio, cada detalhe dessa região que amamos. Não somos apenas corretores — somos moradores, vizinhos, parte da comunidade.',
                        'text_2' => 'Essa proximidade nos permite oferecer insights reais sobre valorização, qualidade de vida e o potencial de cada imóvel.',
                        'regions' => ['Alphaville', 'Tamboré'],
                        'images' => ['main' => null, 'square' => null, 'wide' => null],
                    ],
                ],
            ]);
        } else {
            $about->template = 'about';
            $about->titulo = $about->titulo ?: 'Quem Somos';
            if ($about->slug !== 'quem-somos') {
                $about->slug = 'quem-somos';
            }
            if (empty($about->data) || !is_array($about->data)) {
                $about->data = [
                    'hero_title_primary' => 'Conexão patrimonial.',
                    'hero_title_secondary' => 'Valor que permanece.',
                    'hero_subtitle' => 'Há mais de 12 anos conectando pessoas aos melhores endereços com expertise, confiança e sofisticação.',
                    'hero_button_label' => 'Explorar imóveis',
                    'hero_button_url' => '/imoveis',
                    'stats' => [
                        ['value' => '12+', 'label' => 'Anos de mercado'],
                        ['value' => '800+', 'label' => 'Imóveis negociados'],
                        ['value' => '2.350+', 'label' => 'Parceiros de negócios'],
                        ['value' => '100%', 'label' => 'Presença em Alphaville'],
                    ],
                    'essence' => [
                        'kicker' => 'Nossa essência',
                        'title_primary' => 'Construímos relações,',
                        'title_highlight' => 'não apenas negócios',
                        'text_1' => '',
                        'text_2' => '',
                        'bullets' => ['Alto padrão', 'Alphaville e região', 'Atendimento exclusivo'],
                        'badge_value' => '12',
                        'badge_label' => 'ANOS',
                    ],
                    'team' => [
                        'kicker' => 'Nosso time',
                        'title' => 'Quem faz acontecer',
                        'subtitle' => 'Profissionais apaixonados por conectar pessoas aos melhores endereços',
                        'members' => [
                            ['name' => 'Profissional 1', 'role' => 'Cargo', 'photo' => null],
                            ['name' => 'Profissional 2', 'role' => 'Cargo', 'photo' => null],
                        ],
                    ],
                    'quote' => [
                        'text' => 'Um endereço não é apenas um lugar. É onde a vida acontece, onde memórias são criadas, onde histórias começam.',
                        'author' => '',
                        'author_role' => '',
                    ],
                    'pillars' => [
                        ['title' => 'Confiança', 'description' => 'Transparência em cada etapa da negociação, com orientação real e objetiva.'],
                        ['title' => 'Conexão', 'description' => 'Entendemos o momento de cada cliente para sugerir o imóvel certo.'],
                        ['title' => 'Expertise', 'description' => 'Conhecimento de mercado, valorização e regiões para decisões seguras.'],
                        ['title' => 'Valor', 'description' => 'Atendimento cuidadoso e foco em longo prazo, não apenas na venda.'],
                    ],
                    'territory' => [
                        'kicker' => 'Nosso território',
                        'title' => 'Alphaville é',
                        'title_highlight' => 'nossa casa',
                        'text_1' => 'Conhecemos cada rua, cada condomínio, cada detalhe dessa região que amamos. Não somos apenas corretores — somos moradores, vizinhos, parte da comunidade.',
                        'text_2' => 'Essa proximidade nos permite oferecer insights reais sobre valorização, qualidade de vida e o potencial de cada imóvel.',
                        'regions' => ['Alphaville', 'Tamboré'],
                        'images' => ['main' => null, 'square' => null, 'wide' => null],
                    ],
                ];
            }
        }

        $svg = function (string $label, int $w, int $h, string $c1, string $c2): string {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">'
                . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
                . '<stop offset="0" stop-color="' . $c1 . '"/><stop offset="1" stop-color="' . $c2 . '"/></linearGradient></defs>'
                . '<rect width="' . $w . '" height="' . $h . '" fill="url(#g)"/>'
                . '<text x="' . (int) ($w / 2) . '" y="' . (int) ($h / 2) . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="' . max(18, (int) ($w / 24)) . '" fill="rgba(255,255,255,0.55)">' . $safeLabel . '</text>'
                . '</svg>';
            return 'data:image/svg+xml,' . rawurlencode($svg);
        };

        $sampleAboutData = [
            'hero_title_primary' => 'Conexão patrimonial.',
            'hero_title_secondary' => 'Valor que permanece.',
            'hero_subtitle' => 'Especialistas em alto padrão, com curadoria, estratégia e atendimento humano do primeiro contato ao pós-venda.',
            'hero_button_label' => 'Explorar imóveis',
            'hero_button_url' => '/imoveis',
            'hero_background_image' => $svg('Banner (Quem Somos)', 1600, 900, '#0f172a', '#1e3a8a'),
            'stats' => [
                ['value' => '14+', 'label' => 'Anos de experiência'],
                ['value' => '1.240+', 'label' => 'Imóveis negociados'],
                ['value' => '86%', 'label' => 'Clientes por indicação'],
                ['value' => '28', 'label' => 'Bairros atendidos'],
            ],
            'essence' => [
                'kicker' => 'Nossa essência',
                'title_primary' => 'Construímos relações,',
                'title_highlight' => 'não apenas negócios',
                'text_1' => 'Cada imóvel tem uma história, e cada cliente tem um momento. Nossa missão é aproximar os dois com clareza, confiança e agilidade.',
                'text_2' => 'Usamos dados, experiência local e um olhar apurado para antecipar oportunidades e orientar decisões com segurança.',
                'bullets' => ['Curadoria premium', 'Atuação local', 'Atendimento exclusivo'],
                'badge_value' => '14',
                'badge_label' => 'ANOS',
                'image' => $svg('Imagem da Essência', 1400, 900, '#111827', '#334155'),
            ],
            'team' => [
                'kicker' => 'Nosso time',
                'title' => 'Quem faz acontecer',
                'subtitle' => 'Profissionais especialistas em negociação, marketing e relacionamento, focados em resultados e experiência.',
                'members' => [
                    ['name' => 'Marina Duarte', 'role' => 'Especialista em Alto Padrão', 'photo' => $svg('Marina', 600, 600, '#0b1220', '#1e293b')],
                    ['name' => 'Henrique Lima', 'role' => 'Consultor de Negócios', 'photo' => $svg('Henrique', 600, 600, '#0b1220', '#334155')],
                ],
            ],
            'quote' => [
                'text' => 'Um endereço não é apenas um lugar. É onde a vida acontece, onde memórias são criadas e onde histórias começam.',
                'author' => 'Equipe Conecta',
                'author_role' => 'Imobiliária',
            ],
            'pillars' => [
                ['title' => 'Confiança', 'description' => 'Transparência, comunicação direta e documentação acompanhada do início ao fim.', 'icon' => $svg('C', 128, 128, '#1e3a8a', '#0f172a')],
                ['title' => 'Conexão', 'description' => 'Entendemos necessidades reais para sugerir imóveis que fazem sentido.', 'icon' => $svg('Co', 128, 128, '#f97316', '#7c2d12')],
                ['title' => 'Expertise', 'description' => 'Leitura de mercado, precificação e estratégia de negociação.', 'icon' => $svg('E', 128, 128, '#0ea5e9', '#0f172a')],
                ['title' => 'Valor', 'description' => 'Atendimento cuidadoso, com foco em longo prazo e experiência impecável.', 'icon' => $svg('V', 128, 128, '#22c55e', '#064e3b')],
            ],
            'territory' => [
                'kicker' => 'Nosso território',
                'title' => 'Alphaville é',
                'title_highlight' => 'nossa casa',
                'text_1' => 'Conhecemos cada rua, cada condomínio e cada detalhe da região. Não somos apenas corretores — somos parte da comunidade.',
                'text_2' => 'Isso nos permite oferecer insights reais sobre valorização, mobilidade, lifestyle e o potencial de cada imóvel.',
                'regions' => ['Alphaville', 'Tamboré', 'Aldeia da Serra', 'Barueri'],
                'images' => [
                    'main' => $svg('Território (Vertical)', 900, 1200, '#0f172a', '#1e293b'),
                    'square' => $svg('Território (Quadrado)', 900, 900, '#111827', '#334155'),
                    'wide' => $svg('Território (Horizontal)', 1200, 900, '#0b1220', '#1e3a8a'),
                ],
            ],
        ];

        $fillEmpty = function ($current, $sample) use (&$fillEmpty) {
            if (is_array($sample)) {
                $out = is_array($current) ? $current : [];
                foreach ($sample as $k => $v) {
                    $out[$k] = $fillEmpty($out[$k] ?? null, $v);
                }
                return $out;
            }

            if ($current === null) {
                return $sample;
            }

            if (is_string($current) && trim($current) === '') {
                return $sample;
            }

            return $current;
        };

        $about->data = $fillEmpty($about->data ?? [], $sampleAboutData);
        $about->save();
        $this->syncMenuItemForPage($about);

        $contact = Page::firstOrNew(['slug' => 'contato']);
        if (!$contact->exists) {
            $contact->fill([
                'titulo' => 'Contato',
                'template' => 'contact',
                'conteudo' => '<h1>Contato</h1><p>Entre em contato conosco.</p>',
                'ativo' => true,
            ]);
        } else {
            $contact->template = 'contact';
            $contact->titulo = $contact->titulo ?: 'Contato';
        }
        $contact->save();
        $this->syncMenuItemForPage($contact);

        $privacy = Page::firstOrNew(['slug' => 'politicas-de-privacidade']);
        if (!$privacy->exists) {
            $privacy->fill([
                'titulo' => 'Políticas de Privacidade',
                'template' => 'default',
                'conteudo' => '<h1>Políticas de Privacidade</h1><p>Edite este conteúdo no painel administrativo.</p>',
                'ativo' => true,
            ]);
        } else {
            $privacy->template = $privacy->template ?: 'default';
            $privacy->titulo = $privacy->titulo ?: 'Políticas de Privacidade';
        }
        $privacy->save();
        $this->syncMenuItemForPage($privacy);

        $pages = Page::orderBy('titulo')->get();
        return Inertia::render('Admin/Pages', ['pages' => $pages]);
    }

    public function createPage(): Response
    {
        return Inertia::render('Admin/PageCreate');
    }

    public function storePage(Request $request)
    {
        $template = (string) ($request->input('template') ?? 'default');

        $rules = [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:pages,slug'],
            'template' => ['nullable', 'string', 'max:50'],
            'conteudo' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'banner_title' => ['nullable', 'string', 'max:255'],
            'banner_subtitle' => ['nullable', 'string', 'max:500'],
            'banner_image' => ['nullable', 'string', 'max:500'],
            'banner_title_color' => ['nullable', 'string', 'max:20'],
            'banner_subtitle_color' => ['nullable', 'string', 'max:20'],
            'banner_overlay_color' => ['nullable', 'string', 'max:20'],
            'banner_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'banner_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ];

        if ($template === 'default') {
            $rules['conteudo'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $page = Page::create([
            'titulo' => $validated['titulo'],
            'slug' => $validated['slug'],
            'template' => $validated['template'] ?? 'default',
            'conteudo' => $validated['conteudo'] ?? '',
            'data' => $validated['data'] ?? null,
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_subtitle' => $validated['banner_subtitle'] ?? null,
            'banner_image' => $validated['banner_image'] ?? null,
            'banner_title_color' => $validated['banner_title_color'] ?? '#ffffff',
            'banner_subtitle_color' => $validated['banner_subtitle_color'] ?? '#ffffff',
            'banner_overlay_color' => $validated['banner_overlay_color'] ?? '#0f172a',
            'banner_overlay_opacity' => $validated['banner_overlay_opacity'] ?? 70,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'ativo' => $validated['ativo'] ?? true,
        ]);

        if ($request->hasFile('banner_image_file')) {
            $file = $request->file('banner_image_file');
            $path = Storage::disk('public')->putFile("pages/{$page->id}", $file);
            $page->update(['banner_image' => url('/storage/' . $path)]);
        }

        $this->syncMenuItemForPage($page);

        return Redirect::route('admin.pages');
    }
    
    public function editPage(Page $page): Response
    {
        return Inertia::render('Admin/PageEdit', ['page' => $page]);
    }

    public function updatePage(Request $request, Page $page)
    {
        $template = (string) ($request->input('template') ?? $page->template ?? 'default');

        $rules = [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:pages,slug,' . $page->id],
            'template' => ['nullable', 'string', 'max:50'],
            'conteudo' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'banner_title' => ['nullable', 'string', 'max:255'],
            'banner_subtitle' => ['nullable', 'string', 'max:500'],
            'banner_image' => ['nullable', 'string', 'max:500'],
            'banner_title_color' => ['nullable', 'string', 'max:20'],
            'banner_subtitle_color' => ['nullable', 'string', 'max:20'],
            'banner_overlay_color' => ['nullable', 'string', 'max:20'],
            'banner_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'banner_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ];

        if ($template === 'default') {
            $rules['conteudo'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $page->update([
            'titulo' => $validated['titulo'],
            'slug' => $validated['slug'],
            'template' => $validated['template'] ?? $page->template ?? 'default',
            'conteudo' => $validated['conteudo'] ?? $page->conteudo,
            'data' => $validated['data'] ?? $page->data,
            'banner_title' => $validated['banner_title'] ?? null,
            'banner_subtitle' => $validated['banner_subtitle'] ?? null,
            'banner_image' => $validated['banner_image'] ?? null,
            'banner_title_color' => $validated['banner_title_color'] ?? '#ffffff',
            'banner_subtitle_color' => $validated['banner_subtitle_color'] ?? '#ffffff',
            'banner_overlay_color' => $validated['banner_overlay_color'] ?? '#0f172a',
            'banner_overlay_opacity' => $validated['banner_overlay_opacity'] ?? 70,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'ativo' => $validated['ativo'] ?? false,
        ]);

        if ($request->hasFile('banner_image_file')) {
            $file = $request->file('banner_image_file');
            $path = Storage::disk('public')->putFile("pages/{$page->id}", $file);
            $page->update(['banner_image' => url('/storage/' . $path)]);
        }

        $this->syncMenuItemForPage($page);

        return Redirect::route('admin.pages.edit', ['page' => $page->id]);
    }

    public function destroyPage(Page $page)
    {
        if ($page->slug === 'home') {
            return Redirect::route('admin.pages');
        }

        MenuItem::where('url', $this->pageMenuUrl($page->slug))->delete();
        $page->delete();

        return Redirect::route('admin.pages');
    }

    public function duplicatePage(Page $page)
    {
        $slugBase = $page->slug . '-copia';
        $slug = $slugBase;
        $suffix = 2;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $suffix;
            $suffix++;
        }

        $copy = Page::create([
            'titulo' => $page->titulo . ' (Cópia)',
            'slug' => $slug,
            'template' => $page->template ?? 'default',
            'conteudo' => $page->conteudo,
            'data' => $page->data,
            'banner_title' => $page->banner_title,
            'banner_subtitle' => $page->banner_subtitle,
            'banner_image' => $page->banner_image,
            'banner_title_color' => $page->banner_title_color,
            'banner_subtitle_color' => $page->banner_subtitle_color,
            'banner_overlay_color' => $page->banner_overlay_color,
            'banner_overlay_opacity' => $page->banner_overlay_opacity,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'ativo' => $page->ativo,
        ]);

        $this->syncMenuItemForPage($copy);

        return Redirect::route('admin.pages.edit', ['page' => $copy->id]);
    }

    public function uploadPageMedia(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);

        $file = $validated['file'];
        $path = Storage::disk('public')->putFile("pages/{$page->id}/media", $file);

        return response()->json([
            'url' => url('/storage/' . $path),
        ]);
    }
    
    public function settings(): Response
    {
        $settings = Setting::all()->pluck('valor', 'chave');
        return Inertia::render('Admin/Settings', ['settings' => $settings]);
    }

    public function updateSettings(Request $request)
    {
        $isAdmin = $request->user() && $request->user()->role === 'admin';

        $validated = $request->validate([
            'nome_empresa' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:100'],
            'email_contato' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:100'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'admin_path' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-]*$/',
                Rule::notIn(['/', 'storage', 'feed', 'imoveis', 'blog', 'contato']),
            ],
            'login_path' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-]*$/',
                Rule::notIn(['/', 'storage', 'feed', 'imoveis', 'blog', 'contato']),
            ],
            'about_hero_title_primary' => ['nullable', 'string', 'max:255'],
            'about_hero_title_secondary' => ['nullable', 'string', 'max:255'],
            'about_hero_subtitle' => ['nullable', 'string', 'max:500'],
            'about_hero_button_label' => ['nullable', 'string', 'max:100'],
            'about_hero_button_url' => ['nullable', 'string', 'max:255'],
            'about_hero_background_image' => ['nullable', 'string', 'max:500'],
            'about_hero_background_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'about_stat_1_value' => ['nullable', 'string', 'max:50'],
            'about_stat_1_label' => ['nullable', 'string', 'max:100'],
            'about_stat_2_value' => ['nullable', 'string', 'max:50'],
            'about_stat_2_label' => ['nullable', 'string', 'max:100'],
            'about_stat_3_value' => ['nullable', 'string', 'max:50'],
            'about_stat_3_label' => ['nullable', 'string', 'max:100'],
            'about_stat_4_value' => ['nullable', 'string', 'max:50'],
            'about_stat_4_label' => ['nullable', 'string', 'max:100'],
            'about_essence_kicker' => ['nullable', 'string', 'max:100'],
            'about_essence_title_primary' => ['nullable', 'string', 'max:255'],
            'about_essence_title_highlight' => ['nullable', 'string', 'max:255'],
            'about_essence_text_1' => ['nullable', 'string', 'max:2000'],
            'about_essence_text_2' => ['nullable', 'string', 'max:2000'],
            'about_essence_bullet_1' => ['nullable', 'string', 'max:100'],
            'about_essence_bullet_2' => ['nullable', 'string', 'max:100'],
            'about_essence_bullet_3' => ['nullable', 'string', 'max:100'],
            'about_essence_badge_value' => ['nullable', 'string', 'max:20'],
            'about_essence_badge_label' => ['nullable', 'string', 'max:20'],
            'about_essence_image' => ['nullable', 'string', 'max:500'],
            'about_essence_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'about_team_kicker' => ['nullable', 'string', 'max:100'],
            'about_team_title' => ['nullable', 'string', 'max:255'],
            'about_team_subtitle' => ['nullable', 'string', 'max:255'],
            'about_team_member_1_name' => ['nullable', 'string', 'max:100'],
            'about_team_member_1_role' => ['nullable', 'string', 'max:100'],
            'about_team_member_1_photo' => ['nullable', 'string', 'max:500'],
            'about_team_member_1_photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'about_team_member_2_name' => ['nullable', 'string', 'max:100'],
            'about_team_member_2_role' => ['nullable', 'string', 'max:100'],
            'about_team_member_2_photo' => ['nullable', 'string', 'max:500'],
            'about_team_member_2_photo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (!$isAdmin) {
            unset($validated['admin_path'], $validated['login_path']);
        }

        if ($isAdmin && !empty($validated['admin_path']) && !empty($validated['login_path']) && $validated['admin_path'] === $validated['login_path']) {
            return Redirect::back()->withErrors([
                'login_path' => 'O link do login não pode ser igual ao link do painel.',
            ]);
        }

        $fileMap = [
            'about_hero_background_image_file' => 'about_hero_background_image',
            'about_essence_image_file' => 'about_essence_image',
            'about_team_member_1_photo_file' => 'about_team_member_1_photo',
            'about_team_member_2_photo_file' => 'about_team_member_2_photo',
        ];

        foreach ($fileMap as $fileKey => $settingKey) {
            if (!$request->hasFile($fileKey)) {
                continue;
            }

            $file = $request->file($fileKey);
            $path = Storage::disk('public')->putFile('site/about', $file);
            $url = url('/storage/' . $path);
            Setting::updateOrCreate(['chave' => $settingKey], ['valor' => (string) $url]);
        }

        foreach ($validated as $key => $value) {
            if (array_key_exists($key, $fileMap)) {
                continue;
            }

            Setting::updateOrCreate(
                ['chave' => $key],
                ['valor' => (string) ($value ?? '')]
            );
        }

        $adminPath = 'admin';
        try {
            $adminPath = trim((string) (Setting::where('chave', 'admin_path')->value('valor') ?: 'admin'), '/');
        } catch (\Throwable) {
            $adminPath = 'admin';
        }
        $adminPath = $adminPath !== '' ? $adminPath : 'admin';

        return Redirect::to('/' . $adminPath . '/settings');
    }

    public function users(): Response
    {
        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'role', 'admin_enabled', 'created_at']);

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function createUser(): Response
    {
        return Inertia::render('Admin/UserCreate');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'admin_enabled' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->adminPermissionKeys())],
        ]);

        $permissions = collect($validated['permissions'] ?? [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'admin_enabled' => (bool) $validated['admin_enabled'],
            'permissions' => $permissions,
            'password' => $validated['password'],
        ]);

        return Redirect::route('admin.users');
    }

    public function editUser(User $user): Response
    {
        return Inertia::render('Admin/UserEdit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'admin_enabled' => (bool) $user->admin_enabled,
                'permissions' => is_array($user->permissions) ? $user->permissions : [],
                'profile_photo_url' => !empty($user->profile_photo_path) ? url('/storage/' . ltrim($user->profile_photo_path, '/')) : null,
            ],
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'admin_enabled' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->adminPermissionKeys())],
        ]);

        $permissions = collect($validated['permissions'] ?? [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'admin_enabled' => (bool) $validated['admin_enabled'],
            'permissions' => $permissions,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return Redirect::route('admin.users');
    }

    private function adminPermissionKeys(): array
    {
        return [
            'dashboard',
            'properties',
            'business_types',
            'pages',
            'appearance',
            'leads',
            'settings',
            'instagram',
            'users',
        ];
    }

    public function destroyUser(User $user)
    {
        if ((int) $user->id === (int) Auth::id()) {
            return Redirect::back()->withErrors([
                'user' => 'Você não pode excluir seu próprio usuário.',
            ]);
        }

        $user->delete();

        return Redirect::route('admin.users');
    }

    public function instagram(): Response
    {
        $settings = Setting::query()->pluck('valor', 'chave');
        $feed = [];

        if (!empty($settings['instagram_feed_json'])) {
            $decoded = json_decode($settings['instagram_feed_json'], true);
            if (is_array($decoded)) {
                $feed = $decoded;
            }
        }

        return Inertia::render('Admin/Instagram', [
            'settings' => $settings,
            'instagramFeed' => $feed,
        ]);
    }

    public function updateInstagram(Request $request)
    {
        $validated = $request->validate([
            'instagram_username' => ['nullable', 'string', 'max:100'],
            'instagram_user_id' => ['nullable', 'string', 'max:100'],
            'instagram_access_token' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['chave' => $key],
                ['valor' => (string) ($value ?? '')]
            );
        }

        return Redirect::route('admin.instagram');
    }

    public function refreshInstagramFeed()
    {
        $settings = Setting::query()->pluck('valor', 'chave');
        $userId = $settings['instagram_user_id'] ?? null;
        $token = $settings['instagram_access_token'] ?? null;

        if (empty($userId) || empty($token)) {
            return Redirect::route('admin.instagram');
        }

        $url = "https://graph.instagram.com/{$userId}/media?fields=id,media_type,media_url,thumbnail_url,permalink,caption,timestamp&access_token={$token}";

        try {
            $response = file_get_contents($url);
            $payload = json_decode($response ?: '[]', true);
            $data = $payload['data'] ?? [];

            if (is_array($data)) {
                Setting::updateOrCreate(
                    ['chave' => 'instagram_feed_json'],
                    ['valor' => json_encode(array_slice($data, 0, 30))]
                );
                Setting::updateOrCreate(
                    ['chave' => 'instagram_last_refresh'],
                    ['valor' => now()->toDateTimeString()]
                );
            }
        } catch (\Throwable) {
            Setting::updateOrCreate(
                ['chave' => 'instagram_last_refresh'],
                ['valor' => now()->toDateTimeString()]
            );
        }

        return Redirect::route('admin.instagram');
    }

    private function syncMenuItemForPage(Page $page): void
    {
        $url = $this->pageMenuUrl($page->slug);

        if ($url === '/') {
            return;
        }

        if (!$page->ativo) {
            MenuItem::where('url', $url)->delete();
            return;
        }

        MenuItem::updateOrCreate(
            ['url' => $url],
            [
                'label' => $page->titulo,
                'icon' => 'tag',
                'url' => $url,
                'order' => 50,
                'is_active' => true,
            ]
        );
    }

    private function pageMenuUrl(string $slug): string
    {
        if ($slug === 'home') {
            return '/';
        }
        if ($slug === 'sobre' || $slug === 'quem-somos') {
            return '/quem-somos';
        }
        if ($slug === 'contato') {
            return '/contato';
        }

        return '/' . $slug;
    }

    public function businessTypes(): Response
    {
        $items = BusinessType::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/BusinessTypes', [
            'items' => $items,
        ]);
    }

    public function storeBusinessType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], BusinessType::class);

        BusinessType::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return Redirect::route('admin.business-types');
    }

    public function updateBusinessType(Request $request, BusinessType $businessType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], BusinessType::class, $businessType->id);

        $businessType->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? $businessType->is_active,
            'sort_order' => $validated['sort_order'] ?? $businessType->sort_order,
        ]);

        return Redirect::route('admin.business-types');
    }

    public function destroyBusinessType(BusinessType $businessType)
    {
        $businessType->delete();

        return Redirect::route('admin.business-types');
    }

    public function propertyTypes(): Response
    {
        $items = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get();

        return Inertia::render('Admin/PropertyTypes', [
            'items' => $items,
        ]);
    }

    public function storePropertyType(Request $request)
    {
        $validated = $request->validate([
            'nome_tipo' => ['required', 'string', 'max:255'],
            'nome_subtipo' => ['nullable', 'string', 'max:255'],
        ]);

        $label = $validated['nome_subtipo'] ? ($validated['nome_tipo'] . ' ' . $validated['nome_subtipo']) : $validated['nome_tipo'];
        $slug = $this->uniqueSlug($label, PropertyType::class);

        PropertyType::create([
            'nome_tipo' => $validated['nome_tipo'],
            'nome_subtipo' => $validated['nome_subtipo'] ?? null,
            'slug' => $slug,
        ]);

        return Redirect::route('admin.property-types');
    }

    public function updatePropertyType(Request $request, PropertyType $propertyType)
    {
        $validated = $request->validate([
            'nome_tipo' => ['required', 'string', 'max:255'],
            'nome_subtipo' => ['nullable', 'string', 'max:255'],
        ]);

        $label = $validated['nome_subtipo'] ? ($validated['nome_tipo'] . ' ' . $validated['nome_subtipo']) : $validated['nome_tipo'];
        $slug = $this->uniqueSlug($label, PropertyType::class, $propertyType->id);

        $propertyType->update([
            'nome_tipo' => $validated['nome_tipo'],
            'nome_subtipo' => $validated['nome_subtipo'] ?? null,
            'slug' => $slug,
        ]);

        return Redirect::route('admin.property-types');
    }

    public function destroyPropertyType(PropertyType $propertyType)
    {
        $propertyType->delete();

        return Redirect::route('admin.property-types');
    }

    public function specialCategories(): Response
    {
        $items = SpecialCategory::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/SpecialCategories', [
            'items' => $items,
        ]);
    }

    public function storeSpecialCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], SpecialCategory::class);

        SpecialCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return Redirect::route('admin.special-categories');
    }

    public function updateSpecialCategory(Request $request, SpecialCategory $specialCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], SpecialCategory::class, $specialCategory->id);

        $specialCategory->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? $specialCategory->is_active,
            'sort_order' => $validated['sort_order'] ?? $specialCategory->sort_order,
        ]);

        return Redirect::route('admin.special-categories');
    }

    public function destroySpecialCategory(SpecialCategory $specialCategory)
    {
        $specialCategory->delete();

        return Redirect::route('admin.special-categories');
    }

    public function blogCategories(): Response
    {
        $items = BlogCategory::orderBy('name')->get();

        return Inertia::render('Admin/Blog/Categories', [
            'items' => $items,
        ]);
    }

    public function storeBlogCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], BlogCategory::class);

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return Redirect::route('admin.blog.categories');
    }

    public function updateBlogCategory(Request $request, BlogCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], BlogCategory::class, $category->id);

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return Redirect::route('admin.blog.categories');
    }

    public function destroyBlogCategory(BlogCategory $category)
    {
        $category->delete();

        return Redirect::route('admin.blog.categories');
    }

    public function blogPosts(): Response
    {
        $posts = BlogPost::with('category')->orderByDesc('created_at')->get();
        $categories = BlogCategory::orderBy('name')->get();

        return Inertia::render('Admin/Blog/Posts', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }

    public function createBlogPost(): Response
    {
        $categories = BlogCategory::orderBy('name')->get();

        return Inertia::render('Admin/Blog/PostCreate', [
            'categories' => $categories,
        ]);
    }

    public function storeBlogPost(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = $this->uniqueSlug($validated['title'], BlogPost::class);

        $post = BlogPost::create([
            ...collect($validated)->except(['featured_image'])->all(),
            'slug' => $slug,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $path = Storage::disk('public')->putFile("blog/{$post->id}", $file);
            $post->update([
                'featured_image' => url('/storage/' . $path),
            ]);
        }

        return Redirect::route('admin.blog.posts');
    }

    public function editBlogPost(BlogPost $post): Response
    {
        $categories = BlogCategory::orderBy('name')->get();

        return Inertia::render('Admin/Blog/PostEdit', [
            'post' => $post->load('category'),
            'categories' => $categories,
        ]);
    }

    public function updateBlogPost(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = $this->uniqueSlug($validated['title'], BlogPost::class, $post->id);

        $post->update([
            ...collect($validated)->except(['featured_image'])->all(),
            'slug' => $slug,
            'is_featured' => $validated['is_featured'] ?? false,
        ]);

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $path = Storage::disk('public')->putFile("blog/{$post->id}", $file);
            $post->update([
                'featured_image' => url('/storage/' . $path),
            ]);
        }

        return Redirect::route('admin.blog.posts');
    }

    public function destroyBlogPost(BlogPost $post)
    {
        $post->delete();

        return Redirect::route('admin.blog.posts');
    }

    private function uniqueSlug(string $value, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $suffix = 2;

        while (true) {
            $query = $modelClass::where('slug', $slug);

            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                return $slug;
            }

            $slug = $base . '-' . $suffix;
            $suffix++;
        }
    }
}
