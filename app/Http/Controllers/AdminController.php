<?php

namespace App\Http\Controllers;

use App\Actions\Properties\AttachPropertyImageUploadsAction;
use App\Actions\Properties\StagePropertyImageUploadAction;
use App\Http\Requests\Admin\StagePropertyImageUploadRequest;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdateProfileAvatarRequest;
use App\Http\Requests\Admin\UpdateProfileInfoRequest;
use App\Http\Requests\Admin\UpdateProfilePasswordRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Jobs\ProcessPropertyImageJob;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\BusinessType;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Condominium;
use App\Models\Property;
use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Models\PropertyType;
use App\Models\SpecialCategory;
use App\Models\Lead;
use App\Models\MenuItem;
use App\Models\Setting;
use App\Models\Page;
use App\Models\User;
use App\Support\PropertyDescriptionSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Throwable;

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
        if (env('TRAE_DEBUG_ADMIN_AUTH_UPLOAD_419')) {
            // #region debug-point A:login-request-enter
            rescue(function () use ($request): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'admin-auth-upload-419',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/AdminController.php:login-enter',
                    'msg' => '[DEBUG] Admin login request entered',
                    'data' => [
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'has_session_cookie' => $request->cookies->has(config('session.cookie')),
                        'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                        'has_x_csrf_token_header' => $request->headers->has('X-CSRF-TOKEN'),
                        'has_x_xsrf_token_header' => $request->headers->has('X-XSRF-TOKEN'),
                        'origin' => $request->headers->get('origin'),
                        'referer' => $request->headers->get('referer'),
                        'user_agent' => $request->userAgent(),
                        'session_id' => $request->session()->getId(),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($validated['remember'] ?? false);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $remember)) {
            if (env('TRAE_DEBUG_ADMIN_AUTH_UPLOAD_419')) {
                // #region debug-point A:login-attempt-failed
                rescue(function () use ($request, $validated): void {
                    Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                        'sessionId' => 'admin-auth-upload-419',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'A',
                        'location' => 'app/Http/Controllers/AdminController.php:login-failed',
                        'msg' => '[DEBUG] Admin login credentials rejected',
                        'data' => [
                            'email' => $validated['email'] ?? null,
                            'session_id' => $request->session()->getId(),
                        ],
                        'ts' => (int) round(microtime(true) * 1000),
                    ]);
                }, report: false);
                // #endregion
            }

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

        if (env('TRAE_DEBUG_ADMIN_AUTH_UPLOAD_419')) {
            // #region debug-point A:login-success
            rescue(function () use ($request): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'admin-auth-upload-419',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/AdminController.php:login-success',
                    'msg' => '[DEBUG] Admin login succeeded',
                    'data' => [
                        'user_id' => $request->user()?->id,
                        'session_id' => $request->session()->getId(),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

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
            'user' => $user ? $this->serializeProfileUser($user) : null,
        ]);
    }

    public function updateProfileInfo(UpdateProfileInfoRequest $request): JsonResponse
    {
        $user = User::query()->find(Auth::id());
        if (!$user) {
            abort(403);
        }

        $validated = $request->validated();
        $emailChanged = $validated['email'] !== $user->email;

        DB::transaction(function () use ($user, $validated, $emailChanged): void {
            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if ($emailChanged) {
                $payload['email_verified_at'] = null;
            }

            $user->update($payload);
        });

        $user->refresh();

        if ($emailChanged && $user instanceof MustVerifyEmail && method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => $emailChanged
                ? 'Perfil atualizado com sucesso. O e-mail precisa ser verificado novamente.'
                : 'Perfil atualizado com sucesso.',
            'user' => $this->serializeProfileUser($user),
        ]);
    }

    public function updateProfilePassword(UpdateProfilePasswordRequest $request): JsonResponse
    {
        $user = User::query()->find(Auth::id());
        if (!$user) {
            abort(403);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated): void {
            $user->update([
                'password' => $validated['password'],
            ]);
        });

        Auth::logoutOtherDevices($validated['current_password']);

        return response()->json([
            'message' => 'Senha atualizada com sucesso.',
        ]);
    }

    public function updateProfileAvatar(UpdateProfileAvatarRequest $request): JsonResponse
    {
        $user = User::query()->find(Auth::id());
        if (!$user) {
            abort(403);
        }

        $file = $request->file('profile_photo');
        $newPath = Storage::disk('public')->putFile("profiles/{$user->id}", $file);
        $oldPath = $user->profile_photo_path;

        DB::transaction(function () use ($user, $newPath): void {
            $user->update([
                'profile_photo_path' => $newPath,
            ]);
        });

        if (!empty($oldPath) && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $user->refresh();

        return response()->json([
            'message' => 'Avatar atualizado com sucesso.',
            'user' => $this->serializeProfileUser($user),
        ]);
    }

    private function serializeProfileUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'profile_photo_url' => !empty($user->profile_photo_path) ? url('/storage/' . ltrim($user->profile_photo_path, '/')) : null,
        ];
    }

    public function index(): Response
    {
        $dashboardTimezone = 'America/Sao_Paulo';
        $nowLocal = now($dashboardTimezone);
        $monthStart = $nowLocal->copy()->startOfMonth()->subMonths(11);

        $monthLabels = [];
        $monthKeys = [];
        $monthCursor = $monthStart->copy();
        for ($i = 0; $i < 12; $i++) {
            $monthKeys[] = $monthCursor->format('Y-m');
            $monthLabels[] = $this->formatMonthLabelPtBr($monthCursor);
            $monthCursor->addMonth();
        }

        $presetRaw = trim((string) request('preset', ''));
        $startRaw = trim((string) request('start', ''));
        $endRaw = trim((string) request('end', ''));

        [$rangeStartLocal, $rangeEndLocal, $activePreset] = $this->resolveDashboardRange(
            $dashboardTimezone,
            $nowLocal,
            $presetRaw,
            $startRaw,
            $endRaw
        );

        $rangeDays = max(1, $rangeStartLocal->diffInDays($rangeEndLocal) + 1);
        $prevEndLocal = $rangeStartLocal->copy()->subDay()->endOfDay();
        $prevStartLocal = $prevEndLocal->copy()->subDays($rangeDays - 1)->startOfDay();

        $rangeStart = $rangeStartLocal->copy()->utc();
        $rangeEnd = $rangeEndLocal->copy()->utc();
        $prevStart = $prevStartLocal->copy()->utc();
        $prevEnd = $prevEndLocal->copy()->utc();
        $todayStart = $nowLocal->copy()->startOfDay()->utc();
        $todayEnd = $nowLocal->copy()->endOfDay()->utc();

        $hasExclusive = Schema::hasColumn('properties', 'is_exclusive');
        $hasMetaTitle = Schema::hasColumn('properties', 'meta_title');
        $hasMetaDescription = Schema::hasColumn('properties', 'meta_description');

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

        $leadsToday = Lead::query()->whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $leadsInRange = Lead::query()->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $leadsPrev = Lead::query()->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $contactsInRange = Lead::query()->where('origem', 'Site - Contato')->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $contactsPrev = Lead::query()->where('origem', 'Site - Contato')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $propertyViewsToday = $this->safeCountTableToday('property_views');
        $propertyViewsInRange = $this->safeCountTableBetween('property_views', $rangeStart, $rangeEnd);
        $propertyViewsPrev = $this->safeCountTableBetween('property_views', $prevStart, $prevEnd);

        $kpis = [
            'properties_active' => $propertiesActiveNewInRange,
            'properties_active_delta' => $this->percentDelta($propertiesActiveNewInRange, $propertiesActiveNewPrev),
            'properties_featured' => $propertiesFeaturedNewInRange,
            'properties_featured_delta' => $this->percentDelta($propertiesFeaturedNewInRange, $propertiesFeaturedNewPrev),
            'leads_total' => $leadsInRange,
            'leads_total_delta' => $this->percentDelta($leadsInRange, $leadsPrev),
            'leads_today' => $leadsToday,
            'property_views_total' => $propertyViewsInRange,
            'property_views_total_delta' => $this->percentDelta($propertyViewsInRange, $propertyViewsPrev),
            'contacts_total' => $contactsInRange,
            'contacts_total_delta' => $this->percentDelta($contactsInRange, $contactsPrev),
            'range_label' => $this->dashboardRangeLabel($activePreset),
        ];

        $businessCount = function (string $kind, ?array $range = null): int {
            $query = Property::query()->where('ativo', true);

            if ($range) {
                $query->whereBetween('created_at', $range);
            }

            if ($kind === 'sale') {
                $query->where(function ($sub): void {
                    $sub->where('aceita_venda', true)
                        ->orWhere('operacao', 'Venda');
                });
            } elseif ($kind === 'rent') {
                $query->where(function ($sub): void {
                    $sub->where('aceita_locacao', true)
                        ->orWhere('operacao', 'Aluguel');
                });
            } elseif ($kind === 'season') {
                $query->where(function ($sub): void {
                    $sub->where('aceita_temporada', true)
                        ->orWhere('operacao', 'Temporada');
                });
            }

            return $query->count();
        };

        $propertyStatus = [
            'sale' => $businessCount('sale'),
            'rent' => $businessCount('rent'),
            'season' => $businessCount('season'),
            'exclusive' => $hasExclusive ? Property::query()->where('ativo', true)->where('is_exclusive', true)->count() : 0,
            'inactive' => Property::query()->where('ativo', false)->count(),
            'sale_delta' => $this->percentDelta(
                $businessCount('sale', [$rangeStart, $rangeEnd]),
                $businessCount('sale', [$prevStart, $prevEnd])
            ),
            'rent_delta' => $this->percentDelta(
                $businessCount('rent', [$rangeStart, $rangeEnd]),
                $businessCount('rent', [$prevStart, $prevEnd])
            ),
            'season_delta' => $this->percentDelta(
                $businessCount('season', [$rangeStart, $rangeEnd]),
                $businessCount('season', [$prevStart, $prevEnd])
            ),
            'exclusive_delta' => $hasExclusive ? $this->percentDelta(
                Property::query()->where('ativo', true)->where('is_exclusive', true)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
                Property::query()->where('ativo', true)->where('is_exclusive', true)->whereBetween('created_at', [$prevStart, $prevEnd])->count()
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
                'start' => $rangeStartLocal->toDateString(),
                'end' => $rangeEndLocal->toDateString(),
                'preset' => $activePreset,
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

    private function resolveDashboardRange(
        string $timezone,
        Carbon $nowLocal,
        string $presetRaw,
        string $startRaw,
        string $endRaw
    ): array {
        $allowedPresets = ['today', 'yesterday', 'last_7_days', 'this_month', 'this_year'];
        $preset = in_array($presetRaw, $allowedPresets, true) ? $presetRaw : '';

        if ($preset !== '') {
            $start = $nowLocal->copy()->startOfDay();
            $end = $nowLocal->copy()->endOfDay();

            if ($preset === 'yesterday') {
                $start->subDay()->startOfDay();
                $end->subDay()->endOfDay();
            } elseif ($preset === 'last_7_days') {
                $start->subDays(6)->startOfDay();
            } elseif ($preset === 'this_month') {
                $start->startOfMonth()->startOfDay();
            } elseif ($preset === 'this_year') {
                $start->startOfYear()->startOfDay();
            }

            return [$start, $end, $preset];
        }

        try {
            $start = $startRaw !== ''
                ? Carbon::createFromFormat('Y-m-d', $startRaw, $timezone)->startOfDay()
                : $nowLocal->copy()->startOfMonth()->startOfDay();
        } catch (\Throwable) {
            $start = $nowLocal->copy()->startOfMonth()->startOfDay();
        }

        try {
            $end = $endRaw !== ''
                ? Carbon::createFromFormat('Y-m-d', $endRaw, $timezone)->endOfDay()
                : $nowLocal->copy()->endOfDay();
        } catch (\Throwable) {
            $end = $nowLocal->copy()->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end, ''];
    }

    private function dashboardRangeLabel(string $preset): string
    {
        return match ($preset) {
            'today' => 'hoje',
            'yesterday' => 'ontem',
            'last_7_days' => 'nos ultimos 7 dias',
            'this_month' => 'neste mes',
            'this_year' => 'neste ano',
            default => 'no periodo selecionado',
        };
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
                $photoUrl = $this->propertyPhotoCardUrl($photo);

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
                $photoUrl = $this->propertyPhotoCardUrl($photo);

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
            ->with(['propertyType', 'businessType', 'condominium', 'photos'])
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
            ->with(['propertyType', 'businessType', 'condominium', 'photos'])
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
        $condominiums = Condominium::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $specialCategories = SpecialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/PropertyCreate', [
            'propertyTypes' => $propertyTypes,
            'businessTypes' => $businessTypes,
            'condominiums' => $condominiums,
            'specialCategories' => $specialCategories,
            'imageUploadConfig' => [
                'maxFiles' => config('image_uploads.max_files_per_property'),
                'maxFileSizeBytes' => (int) config('image_uploads.max_file_size_bytes', 50 * 1024 * 1024),
                'parallelUploads' => (int) config('image_uploads.parallel_uploads', 6),
                'pollIntervalMs' => (int) config('image_uploads.poll_interval_ms', 4000),
                'requestMaxBodyHint' => (int) config('image_uploads.request_max_body_hint', 60 * 1024 * 1024),
            ],
        ]);
    }

    public function stagePropertyImageUpload(
        StagePropertyImageUploadRequest $request,
        StagePropertyImageUploadAction $action
    ): JsonResponse {
        Log::info('Request de upload definitivo de imagem recebida.', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'has_session_cookie' => $request->cookies->has(config('session.cookie')),
            'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
            'has_x_csrf_token_header' => $request->headers->has('X-CSRF-TOKEN'),
            'has_x_xsrf_token_header' => $request->headers->has('X-XSRF-TOKEN'),
        ]);

        $upload = $action->execute($request->user(), $request->file('file'));

        Log::info('Upload definitivo concluido com sucesso.', [
            'upload_id' => $upload->id,
            'user_id' => $request->user()?->id,
            'token' => $upload->token,
        ]);

        if (env('TRAE_DEBUG_ADMIN_AUTH_UPLOAD_419')) {
            // #region debug-point B:upload-response
            rescue(function () use ($request, $upload): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'admin-auth-upload-419',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'B',
                    'location' => 'app/Http/Controllers/AdminController.php:stage-upload-response',
                    'msg' => '[DEBUG] Stage upload response prepared',
                    'data' => [
                        'upload_id' => $upload->id,
                        'user_id' => $request->user()?->id,
                        'status' => $upload->status,
                        'mime_type' => $upload->mime_type,
                        'temp_path' => $upload->temp_path,
                        'preview_url' => in_array($upload->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true)
                            ? $this->publicMediaUrl($upload->temp_path)
                            : null,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        return response()->json([
            'token' => $upload->token,
            'name' => $upload->original_name,
            'mime_type' => $upload->mime_type,
            'size' => $upload->size,
            'status' => $upload->status,
            'preview_url' => in_array($upload->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true)
                ? $this->publicMediaUrl($upload->temp_path)
                : null,
            'uploaded' => true,
        ]);
    }

    public function propertyImageProcessingStatus(Property $property): JsonResponse
    {
        $property->load('photos');

        $photos = $property->photos
            ->sortBy(fn (PropertyPhoto $photo) => [$photo->principal ? 0 : 1, $photo->ordem, $photo->id])
            ->values();

        $counts = [
            'total' => $photos->count(),
            'uploaded' => $photos->where('processing_status', 'uploaded')->count(),
            'processing' => $photos->where('processing_status', 'processing')->count(),
            'ready' => $photos->where('processing_status', 'ready')->count(),
            'failed' => $photos->where('processing_status', 'failed')->count(),
        ];

        return response()->json([
            'counts' => $counts,
            'is_processing' => ($counts['uploaded'] + $counts['processing']) > 0,
            'metrics' => [
                'images' => $photos->count(),
                'source_size' => (int) $photos->sum(fn (PropertyPhoto $photo) => (int) ($photo->source_size ?? 0)),
                'optimized_size' => (int) $photos->sum(fn (PropertyPhoto $photo) => (int) ($photo->size ?? 0)),
                'bytes_saved' => (int) $photos->sum(fn (PropertyPhoto $photo) => max(0, (int) ($photo->source_size ?? 0) - (int) ($photo->size ?? 0))),
            ],
            'photos' => $photos->map(fn (PropertyPhoto $photo) => [
                'id' => $photo->id,
                'principal' => (bool) $photo->principal,
                'ordem' => (int) $photo->ordem,
                'url' => $this->propertyPhotoPreviewUrl($photo),
                'original_url' => $this->propertyPhotoRenderableOriginalUrl($photo),
                'medium_url' => $photo->medium_url,
                'thumb_small_url' => $photo->thumb_small_url,
                'size' => $photo->size,
                'source_size' => $photo->source_size,
                'source_mime_type' => $photo->source_mime_type,
                'processing_status' => $photo->processing_status,
                'processing_error' => $photo->processing_error,
            ])->values(),
        ]);
    }

    public function destroyStagedPropertyImage(
        string $token,
        Request $request,
        StagePropertyImageUploadAction $action
    ): JsonResponse {
        $upload = PropertyImageUpload::query()
            ->where('token', $token)
            ->where('user_id', $request->user()?->id)
            ->firstOrFail();

        $action->destroy($upload);

        return response()->json(['deleted' => true]);
    }

    public function reprocessPropertyImage(Property $property, PropertyPhoto $photo): JsonResponse
    {
        abort_unless($photo->property_id === $property->id, 404);

        if (env('TRAE_DEBUG_ADMIN_REPROCESS_FAILURE')) {
            // #region debug-point A:reprocess-enter
            rescue(function () use ($property, $photo): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'admin-reprocess-failure',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/AdminController.php:reprocessPropertyImage:enter',
                    'msg' => '[DEBUG] Reprocess request entered',
                    'data' => [
                        'property_id' => $property->id,
                        'photo_id' => $photo->id,
                        'photo_processing_status' => $photo->processing_status,
                        'photo_processing_error' => $photo->processing_error,
                        'photo_original_path' => $photo->original_path,
                        'photo_arquivo' => $photo->arquivo,
                        'photo_source_mime_type' => $photo->source_mime_type,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        $upload = PropertyImageUpload::query()
            ->where('property_photo_id', $photo->id)
            ->where('property_id', $property->id)
            ->latest('id')
            ->first();

        if (!$upload) {
            $upload = $this->rebuildPropertyUploadForReprocess($property, $photo);
        }

        if (!$upload) {
            return response()->json([
                'message' => 'Nao foi possivel reenfileirar a imagem porque o arquivo original nao foi encontrado no servidor.',
                'error' => 'property_image_original_missing',
            ], 422);
        }

        if (env('TRAE_DEBUG_ADMIN_REPROCESS_FAILURE')) {
            // #region debug-point A:reprocess-upload-found
            rescue(function () use ($property, $photo, $upload): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'admin-reprocess-failure',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/AdminController.php:reprocessPropertyImage:upload-found',
                    'msg' => '[DEBUG] Reprocess found linked upload',
                    'data' => [
                        'property_id' => $property->id,
                        'photo_id' => $photo->id,
                        'upload_id' => $upload->id,
                        'upload_status' => $upload->status,
                        'upload_temp_path' => $upload->temp_path,
                        'upload_mime_type' => $upload->mime_type,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        $photo->update([
            'processing_status' => 'uploaded',
            'processing_error' => null,
        ]);

        $upload->update([
            'status' => 'uploaded',
            'validation_error' => null,
            'attached_at' => now(),
            'processed_at' => null,
        ]);

        ProcessPropertyImageJob::dispatch($photo->id, $upload->id);

        Log::info('Reprocessamento de imagem reenfileirado.', [
            'property_id' => $property->id,
            'photo_id' => $photo->id,
            'upload_id' => $upload->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'requeued' => true,
            'photo_id' => $photo->id,
            'upload_id' => $upload->id,
        ]);
    }

    private function rebuildPropertyUploadForReprocess(Property $property, PropertyPhoto $photo): ?PropertyImageUpload
    {
        $source = $this->findReprocessSourceForPhoto($photo);

        if (!$source) {
            Log::warning('Reprocessamento sem upload vinculado e sem arquivo original recuperavel.', [
                'property_id' => $property->id,
                'photo_id' => $photo->id,
                'original_path' => $photo->original_path,
                'arquivo' => $photo->arquivo,
            ]);

            return null;
        }

        [$diskName, $path] = $source;
        $disk = Storage::disk($diskName);
        $mimeType = (string) ($photo->source_mime_type ?: $photo->mime_type ?: rescue(fn (): string => (string) $disk->mimeType($path), 'application/octet-stream', false));
        $size = (int) ($photo->source_size ?: $photo->size ?: rescue(fn (): int => (int) $disk->size($path), 0, false));

        $upload = PropertyImageUpload::create([
            'user_id' => auth()->id() ?? 1,
            'property_id' => $property->id,
            'property_photo_id' => $photo->id,
            'token' => (string) Str::uuid(),
            'disk' => $diskName,
            'temp_path' => $path,
            'original_name' => basename($path),
            'sanitized_name' => basename($path),
            'extension' => strtolower((string) pathinfo($path, PATHINFO_EXTENSION)),
            'mime_type' => $mimeType,
            'size' => $size,
            'sha256' => hash('sha256', $path . '|' . $photo->id . '|reprocess'),
            'status' => 'uploaded',
            'validation_error' => null,
            'expires_at' => null,
            'processed_at' => null,
            'attached_at' => now(),
        ]);

        Log::info('Upload de recuperacao criado para reprocessamento de foto legada.', [
            'property_id' => $property->id,
            'photo_id' => $photo->id,
            'upload_id' => $upload->id,
            'disk' => $diskName,
            'path' => $path,
        ]);

        return $upload;
    }

    private function findReprocessSourceForPhoto(PropertyPhoto $photo): ?array
    {
        $candidates = [
            [(string) config('image_uploads.original_disk', 'public'), $photo->original_path],
            [(string) config('image_uploads.final_disk', 'public'), $photo->original_path],
            [(string) config('image_uploads.original_disk', 'public'), $photo->arquivo],
            [(string) config('image_uploads.final_disk', 'public'), $photo->arquivo],
        ];

        foreach ($candidates as [$diskName, $path]) {
            $normalizedPath = trim((string) ($path ?? ''), '/');

            if ($normalizedPath === '') {
                continue;
            }

            if (Storage::disk($diskName)->exists($normalizedPath)) {
                return [$diskName, $normalizedPath];
            }
        }

        return null;
    }

    public function storeProperty(
        StorePropertyRequest $request,
        AttachPropertyImageUploadsAction $attachUploads,
        PropertyDescriptionSanitizer $descriptionSanitizer
    )
    {
        $validated = $request->validated();
        $validated['descricao'] = $descriptionSanitizer->sanitize($validated['descricao'] ?? '');

        if (!$descriptionSanitizer->hasVisibleContent($validated['descricao'])) {
            throw ValidationException::withMessages([
                'descricao' => 'Informe uma descricao valida para o imovel.',
            ]);
        }

        try {
            $galleryTokens = $validated['gallery_upload_tokens'] ?? [];
            $this->assertPropertyImageLimit(
                null,
                count($galleryTokens),
                !empty($validated['featured_upload_token'])
            );

            Log::info('Iniciando cadastro de imovel com uploads definitivos.', [
                'user_id' => $request->user()?->id,
                'featured_upload_present' => !empty($validated['featured_upload_token']),
                'gallery_tokens_received' => count($galleryTokens),
                'gallery_tokens_unique' => count(array_unique($galleryTokens)),
            ]);

            $slug = $this->generateUniquePropertySlug($validated['titulo']);
            $codigoAnuncio = $this->generateUniqueCodigoAnuncio();
            $selectedBusinessTypes = $this->resolveSelectedBusinessTypes($validated['business_type_ids'] ?? []);
            $businessPayload = $this->buildBusinessPayload($selectedBusinessTypes, $validated);

            if (!$businessPayload['has_any_business']) {
                throw ValidationException::withMessages([
                    'business_type_ids' => 'Selecione ao menos um tipo de negocio.',
                ]);
            }

            $property = Property::create([
                ...collect($validated)->except([
                    'featured_upload_token',
                    'featured_existing_photo_id',
                    'gallery_upload_tokens',
                    'special_category_ids',
                    'business_type_ids',
                    'valor_venda',
                    'valor_locacao',
                    'valor_condominio',
                    'valor_iptu',
                ])->all(),
                'slug' => $slug,
                'codigo_anuncio' => $codigoAnuncio,
                'moeda' => 'BRL',
                'ativo' => true,
                ...$this->buildPropertyCharacteristicsPayload($validated),
                ...$businessPayload['attributes'],
            ]);

            if (!empty($validated['special_category_ids'])) {
                $property->specialCategories()->sync($validated['special_category_ids']);
            }

            $this->assignSequentialCodigoReferencia($property);

            $attachUploads->execute(
                $property,
                $request->user(),
                $validated['featured_upload_token'] ?? null,
                $galleryTokens
            );

            Log::info('Cadastro de imovel finalizado com uploads vinculados.', [
                'property_id' => $property->id,
                'user_id' => $request->user()?->id,
                'codigo_referencia' => $property->codigo_referencia,
                'property_photos_total' => $property->photos()->count(),
            ]);

            return Redirect::route('admin.properties.edit', ['property' => $property->id])
                ->with('success', 'Imovel cadastrado com sucesso. Codigo gerado automaticamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Falha ao cadastrar imovel.', [
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return Redirect::route('admin.properties')
                ->with('error', 'Nao foi possivel cadastrar o imovel. Tente novamente.');
        }
    }

    public function editProperty(Property $property): Response
    {
        $property->load(['photos', 'specialCategories', 'condominium']);

        $propertyTypes = PropertyType::orderBy('nome_tipo')->orderBy('nome_subtipo')->get();
        $businessTypes = BusinessType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $condominiums = Condominium::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $specialCategories = SpecialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/PropertyCreate', [
            'propertyTypes' => $propertyTypes,
            'businessTypes' => $businessTypes,
            'condominiums' => $condominiums,
            'specialCategories' => $specialCategories,
            'property' => $property,
            'selectedSpecialCategoryIds' => $property->specialCategories->pluck('id')->values(),
            'imageUploadConfig' => [
                'maxFiles' => config('image_uploads.max_files_per_property'),
                'maxFileSizeBytes' => (int) config('image_uploads.max_file_size_bytes', 50 * 1024 * 1024),
                'parallelUploads' => (int) config('image_uploads.parallel_uploads', 6),
                'pollIntervalMs' => (int) config('image_uploads.poll_interval_ms', 4000),
                'requestMaxBodyHint' => (int) config('image_uploads.request_max_body_hint', 60 * 1024 * 1024),
            ],
        ]);
    }

    public function updateProperty(
        UpdatePropertyRequest $request,
        Property $property,
        AttachPropertyImageUploadsAction $attachUploads,
        PropertyDescriptionSanitizer $descriptionSanitizer
    )
    {
        $validated = $request->validated();
        $validated['descricao'] = $descriptionSanitizer->sanitize($validated['descricao'] ?? '');

        if (!$descriptionSanitizer->hasVisibleContent($validated['descricao'])) {
            throw ValidationException::withMessages([
                'descricao' => 'Informe uma descricao valida para o imovel.',
            ]);
        }

        $galleryTokens = $validated['gallery_upload_tokens'] ?? [];
        $this->assertPropertyImageLimit(
            $property,
            count($galleryTokens),
            !empty($validated['featured_upload_token']),
            $validated['remove_photo_ids'] ?? []
        );

        Log::info('Iniciando atualizacao de imovel com uploads definitivos.', [
            'property_id' => $property->id,
            'user_id' => $request->user()?->id,
            'featured_upload_present' => !empty($validated['featured_upload_token']),
            'gallery_tokens_received' => count($galleryTokens),
            'gallery_tokens_unique' => count(array_unique($galleryTokens)),
        ]);

        $selectedBusinessTypes = $this->resolveSelectedBusinessTypes($validated['business_type_ids'] ?? []);
        $businessPayload = $this->buildBusinessPayload($selectedBusinessTypes, $validated);

        if (!$businessPayload['has_any_business']) {
            throw ValidationException::withMessages([
                'business_type_ids' => 'Selecione ao menos um tipo de negocio.',
            ]);
        }

        $property->fill([
            ...collect($validated)->except([
                'featured_upload_token',
                'featured_existing_photo_id',
                'gallery_upload_tokens',
                'remove_photo_ids',
                'photo_order_ids',
                'special_category_ids',
                'business_type_ids',
                'valor_venda',
                'valor_locacao',
                'valor_condominio',
                'valor_iptu',
            ])->all(),
            ...$this->buildPropertyCharacteristicsPayload($validated),
            ...$businessPayload['attributes'],
        ]);
        $property->save();

        if (empty($property->codigo_referencia)) {
            $this->assignSequentialCodigoReferencia($property);
        }

        $property->specialCategories()->sync($validated['special_category_ids'] ?? []);

        $removeIds = collect($validated['remove_photo_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        if (!empty($validated['featured_upload_token'])) {
            $featuredIds = $property->photos()->where('principal', true)->pluck('id');
            $removeIds = $removeIds->merge($featuredIds)->unique()->values();
        }

        if ($removeIds->isNotEmpty()) {
            $photosToRemove = $property->photos()
                ->whereIn('id', $removeIds->all())
                ->get();

            foreach ($photosToRemove as $photo) {
                $this->deletePropertyPhotoAndUpload($photo);
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

        $attachUploads->execute(
            $property,
            $request->user(),
            $validated['featured_upload_token'] ?? null,
            $galleryTokens
        );

        $this->syncFeaturedExistingPhoto($property, $validated['featured_existing_photo_id'] ?? null);

        Log::info('Atualizacao de imovel finalizada com uploads vinculados.', [
            'property_id' => $property->id,
            'user_id' => $request->user()?->id,
            'property_photos_total' => $property->photos()->count(),
        ]);

        return Redirect::route('admin.properties.edit', ['property' => $property->id]);
    }

    public function destroyProperty(Property $property)
    {
        $property->loadMissing(['photos', 'specialCategories', 'features']);
        $this->purgeProperty($property);

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
                $p->loadMissing(['photos', 'specialCategories', 'features']);
                $this->purgeProperty($p);
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
            $this->deletePropertyPhotoAndUpload($photo);
        }

        $this->deleteDetachedPropertyUploads($property);
        $property->specialCategories()->detach();
        $property->features()->detach();
        $property->forceDelete();
    }

    private function deletePropertyPhotoAndUpload(PropertyPhoto $photo): void
    {
        $uploads = PropertyImageUpload::query()
            ->where('property_photo_id', $photo->id)
            ->get();

        Storage::disk((string) config('image_uploads.final_disk', 'public'))->delete(array_filter([
            $photo->arquivo,
            $photo->thumb_small_path,
            $photo->thumb_medium_path,
        ]));

        foreach ($uploads as $upload) {
            Storage::disk((string) $upload->disk)->delete($upload->temp_path);
        }

        if ($uploads->isEmpty() && !empty($photo->original_path)) {
            Storage::disk((string) config('image_uploads.original_disk', 'public'))->delete($photo->original_path);
        }

        PropertyImageUpload::query()
            ->where('property_photo_id', $photo->id)
            ->delete();

        $photo->delete();
    }

    private function deleteDetachedPropertyUploads(Property $property): void
    {
        $uploads = PropertyImageUpload::query()
            ->where('property_id', $property->id)
            ->get();

        foreach ($uploads as $upload) {
            Storage::disk((string) $upload->disk)->delete($upload->temp_path);
        }

        PropertyImageUpload::query()
            ->where('property_id', $property->id)
            ->delete();
    }

    public function duplicateProperty(Property $property)
    {
        $property->load(['photos', 'specialCategories', 'features']);
        $finalDisk = Storage::disk((string) config('image_uploads.final_disk', 'public'));
        $originalDisk = Storage::disk((string) config('image_uploads.original_disk', 'public'));

        $newTitle = trim($property->titulo . ' (Cópia)');
        $new = $property->replicate();
        $new->titulo = $newTitle;
        $new->slug = $this->generateUniquePropertySlug($newTitle);
        $new->codigo_referencia = null;
        $new->codigo_anuncio = $this->generateUniqueCodigoAnuncio();
        $new->ativo = true;
        $new->save();
        $this->assignSequentialCodigoReferencia($new);

        $new->specialCategories()->sync($property->specialCategories->pluck('id')->values()->all());
        $new->features()->sync($property->features->pluck('id')->values()->all());

        foreach ($property->photos as $photo) {
            $source = $photo->arquivo;
            $dest = null;
            $originalDest = null;
            $thumbSmallDest = null;
            $thumbMediumDest = null;

            if (!empty($source) && $finalDisk->exists($source)) {
                $ext = pathinfo($source, PATHINFO_EXTENSION);
                $filename = Str::random(20) . ($ext ? ('.' . $ext) : '');
                $dest = trim((string) config('image_uploads.webp_directory', 'properties/webp'), '/') . "/{$new->id}/{$filename}";
                $finalDisk->copy($source, $dest);
            }

            if (!empty($photo->thumb_small_path) && $finalDisk->exists($photo->thumb_small_path)) {
                $thumbSmallDest = trim((string) config('image_uploads.thumb_directory', 'properties/thumb'), '/') . "/{$new->id}/thumb-small-" . Str::random(20) . '.webp';
                $finalDisk->copy($photo->thumb_small_path, $thumbSmallDest);
            }

            if (!empty($photo->original_path) && $originalDisk->exists($photo->original_path)) {
                $ext = pathinfo($photo->original_path, PATHINFO_EXTENSION);
                $originalDest = trim((string) config('image_uploads.original_directory', 'properties/original'), '/') . "/{$new->id}/original-" . Str::random(20) . ($ext ? ('.' . $ext) : '');
                $originalDisk->copy($photo->original_path, $originalDest);
            }

            if (!empty($photo->thumb_medium_path) && $finalDisk->exists($photo->thumb_medium_path)) {
                $thumbMediumDest = trim((string) config('image_uploads.webp_directory', 'properties/webp'), '/') . "/{$new->id}/thumb-medium-" . Str::random(20) . '.webp';
                $finalDisk->copy($photo->thumb_medium_path, $thumbMediumDest);
            }

            $path = $dest ?: $source;
            $newPhoto = PropertyPhoto::create([
                'property_id' => $new->id,
                'arquivo' => $path,
                'url' => $this->publicMediaUrl($path) ?? '',
                'original_path' => $originalDest,
                'width' => $photo->width,
                'height' => $photo->height,
                'size' => $photo->size,
                'source_size' => $photo->source_size,
                'source_mime_type' => $photo->source_mime_type,
                'mime_type' => $photo->mime_type,
                'thumb_small_path' => $thumbSmallDest,
                'thumb_medium_path' => $thumbMediumDest,
                'optimized' => (bool) $photo->optimized,
                'processed_at' => $photo->processed_at,
                'processing_status' => $photo->processing_status,
                'processing_error' => $photo->processing_error,
                'principal' => (bool) $photo->principal,
                'ordem' => (int) $photo->ordem,
            ]);

            if (!empty($originalDest)) {
                PropertyImageUpload::create([
                    'user_id' => auth()->id() ?? 1,
                    'property_id' => $new->id,
                    'property_photo_id' => $newPhoto->id,
                    'token' => (string) Str::uuid(),
                    'disk' => 'public',
                    'temp_path' => $originalDest,
                    'original_name' => basename($originalDest),
                    'sanitized_name' => basename($originalDest),
                    'extension' => strtolower((string) pathinfo($originalDest, PATHINFO_EXTENSION)),
                    'mime_type' => $photo->source_mime_type ?: $photo->mime_type ?: 'image/jpeg',
                    'size' => (int) ($photo->source_size ?: $photo->size ?: 0),
                    'sha256' => hash('sha256', $originalDest . '|' . $newPhoto->id),
                    'status' => $photo->processing_status === 'failed' ? 'failed' : 'ready',
                    'processed_at' => $photo->processed_at,
                    'attached_at' => now(),
                ]);
            }
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

    private function parseBrlCurrencyNullable(?string $input): ?float
    {
        $raw = trim((string) ($input ?? ''));
        if ($raw === '') {
            return null;
        }

        $value = $this->parseBrlCurrency($raw);

        return $value > 0 ? $value : null;
    }

    private function parseDecimalNullable(mixed $input): ?float
    {
        if ($input === null) {
            return null;
        }

        $raw = trim((string) $input);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $raw);

        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function assertPropertyImageLimit(
        ?Property $property,
        int $newGalleryCount,
        bool $hasFeaturedUpload,
        array $removePhotoIds = []
    ): void {
        $maxFiles = (int) config('image_uploads.max_files_per_property', 200);
        if ($maxFiles <= 0) {
            return;
        }

        $removedIds = collect($removePhotoIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique();

        $currentCount = $property?->photos()->count() ?? 0;
        $existingRemovedCount = $property
            ? $property->photos()->whereIn('id', $removedIds->all())->count()
            : 0;
        $currentFeaturedId = $property?->photos()->where('principal', true)->value('id');
        $featuredWillBeReplaced = $hasFeaturedUpload
            && $currentFeaturedId
            && !$removedIds->contains((int) $currentFeaturedId);

        $projectedCount = $currentCount
            - $existingRemovedCount
            - ($featuredWillBeReplaced ? 1 : 0)
            + max(0, $newGalleryCount)
            + ($hasFeaturedUpload ? 1 : 0);

        if ($projectedCount > $maxFiles) {
            throw ValidationException::withMessages([
                'gallery_upload_tokens' => "Cada imovel pode ter no maximo {$maxFiles} imagens.",
            ]);
        }
    }

    private function syncFeaturedExistingPhoto(Property $property, mixed $featuredExistingPhotoId): void
    {
        $photoId = (int) $featuredExistingPhotoId;
        if ($photoId <= 0) {
            return;
        }

        $selectedPhoto = $property->photos()->find($photoId);
        if (!$selectedPhoto) {
            return;
        }

        DB::transaction(function () use ($property, $selectedPhoto): void {
            $property->photos()->update(['principal' => false]);

            $selectedPhoto->update([
                'principal' => true,
                'ordem' => 0,
            ]);

            $others = $property->photos()
                ->where('id', '!=', $selectedPhoto->id)
                ->orderBy('ordem')
                ->orderBy('id')
                ->get();

            foreach ($others as $index => $photo) {
                $photo->update([
                    'principal' => false,
                    'ordem' => $index + 1,
                ]);
            }
        });
    }

    private function buildPropertyCharacteristicsPayload(array $validated): array
    {
        $areaConstruida = $this->parseDecimalNullable($validated['area_construida'] ?? null);
        $valorCondominio = $this->parseBrlCurrencyNullable($validated['valor_condominio'] ?? null);
        $valorIptu = $this->parseBrlCurrencyNullable($validated['valor_iptu'] ?? null);

        return [
            'area_construida' => $areaConstruida,
            'area_util' => $areaConstruida,
            'valor_condominio' => $valorCondominio,
            'condominio' => $valorCondominio,
            'valor_iptu' => $valorIptu,
            'iptu' => $valorIptu,
        ];
    }

    private function resolveSelectedBusinessTypes(array $ids): \Illuminate\Support\Collection
    {
        $normalizedIds = collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($normalizedIds->isEmpty()) {
            return collect();
        }

        return BusinessType::query()
            ->whereIn('id', $normalizedIds->all())
            ->get(['id', 'name'])
            ->sortBy(fn (BusinessType $type) => $normalizedIds->search($type->id))
            ->values();
    }

    private function buildBusinessPayload(\Illuminate\Support\Collection $businessTypes, array $validated): array
    {
        $selectedNames = $businessTypes
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();

        $salePrice = $this->parseBrlCurrencyNullable($validated['valor_venda'] ?? null);
        $rentPrice = $this->parseBrlCurrencyNullable($validated['valor_locacao'] ?? null);

        $acceptsSale = $selectedNames->contains('Comprar');
        $acceptsRent = $selectedNames->contains('Alugar');
        $acceptsSeason = $selectedNames->contains('Temporada');
        $primaryBusinessType = $businessTypes->first();
        $legacyOperation = $this->mapBusinessTypeNameToLegacyOperacao($primaryBusinessType?->name);
        $legacyValue = $salePrice
            ?? $rentPrice
            ?? ($acceptsSeason ? 0.0 : 0.0);

        return [
            'has_any_business' => $acceptsSale || $acceptsRent || $acceptsSeason,
            'attributes' => [
                'business_type_id' => $primaryBusinessType?->id,
                'operacao' => $legacyOperation,
                'valor' => $legacyValue,
                'valor_venda' => $salePrice,
                'valor_locacao' => $rentPrice,
                'aceita_venda' => $acceptsSale,
                'aceita_locacao' => $acceptsRent,
                'aceita_temporada' => $acceptsSeason,
            ],
        ];
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

    private function assignSequentialCodigoReferencia(Property $property): void
    {
        if (!empty($property->codigo_referencia)) {
            return;
        }

        $property->loadMissing('propertyType');

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $codigo = $this->generateSequentialCodigoReferenciaForProperty($property);

            try {
                $property->forceFill([
                    'codigo_referencia' => $codigo,
                ])->save();

                return;
            } catch (QueryException $e) {
                if (!$this->isCodigoReferenciaUniqueViolation($e)) {
                    throw $e;
                }
            }
        }

        throw ValidationException::withMessages([
            'codigo_referencia' => 'Nao foi possivel gerar um codigo unico para este imovel.',
        ]);
    }

    private function generateSequentialCodigoReferenciaForProperty(Property $property): string
    {
        $prefix = $this->resolveCodigoReferenciaPrefix($property->propertyType);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        $existingCodes = Property::query()
            ->where('id', '!=', $property->id)
            ->whereNotNull('codigo_referencia')
            ->where('codigo_referencia', 'like', $prefix . '%')
            ->pluck('codigo_referencia');

        $maxSequence = 0;

        foreach ($existingCodes as $existingCode) {
            $value = strtoupper(trim((string) $existingCode));

            if (preg_match($pattern, $value, $matches) === 1) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        $nextSequence = $maxSequence + 1;

        do {
            $candidate = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
            $exists = Property::query()
                ->where('id', '!=', $property->id)
                ->where('codigo_referencia', $candidate)
                ->exists();

            if (!$exists) {
                return $candidate;
            }

            $nextSequence++;
        } while (true);
    }

    private function resolveCodigoReferenciaPrefix(?PropertyType $propertyType): string
    {
        $candidates = collect([
            $propertyType?->nome_subtipo,
            $propertyType?->nome_tipo,
            $propertyType?->slug,
        ])
            ->filter(fn ($value) => !empty($value))
            ->map(fn ($value) => Str::lower(Str::ascii(trim((string) $value))))
            ->values();

        $map = [
            'sala comercial' => 'SA',
            'apartamento' => 'AP',
            'casa' => 'CA',
            'chacara' => 'CH',
            'cobertura' => 'CO',
            'fazenda' => 'FA',
            'galpao' => 'GA',
            'terreno' => 'TE',
        ];

        foreach ($map as $label => $prefix) {
            if ($candidates->contains(fn ($candidate) => str_contains($candidate, $label))) {
                return $prefix;
            }
        }

        $fallback = preg_replace('/[^A-Z]/', '', strtoupper(Str::ascii((string) ($propertyType?->slug ?: $propertyType?->nome_tipo ?: 'IM')))) ?? 'IM';
        $fallback = substr($fallback, 0, 2);

        return str_pad($fallback !== '' ? $fallback : 'IM', 2, 'X');
    }

    private function isCodigoReferenciaUniqueViolation(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $message = Str::lower($e->getMessage());

        return in_array($sqlState, ['23000', '23505'], true)
            && str_contains($message, 'codigo_referencia');
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
            'properties_banner_title' => ['nullable', 'string', 'max:255'],
            'properties_banner_subtitle' => ['nullable', 'string', 'max:500'],
            'properties_banner_title_color' => ['nullable', 'string', 'max:20'],
            'properties_banner_subtitle_color' => ['nullable', 'string', 'max:20'],
            'properties_banner_overlay_color' => ['nullable', 'string', 'max:20'],
            'properties_banner_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'properties_banner_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
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
            'properties_banner_title',
            'properties_banner_subtitle',
            'properties_banner_title_color',
            'properties_banner_subtitle_color',
            'properties_banner_overlay_color',
            'properties_banner_overlay_opacity',
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

        if ($request->hasFile('properties_banner_image_file')) {
            $file = $request->file('properties_banner_image_file');
            $path = Storage::disk('public')->putFile('branding/banners', $file);
            Setting::updateOrCreate(['chave' => 'properties_banner_image_url'], ['valor' => url('/storage/' . $path)]);
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
                    'hero_title_primary' => 'Excelência imobiliária.',
                    'hero_title_secondary' => 'Valor que permanece.',
                    'hero_subtitle' => 'Há mais de 12 anos entregando curadoria, confiança e sofisticação nos melhores endereços.',
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
                        'subtitle' => 'Profissionais apaixonados por encontrar os melhores endereços para cada cliente',
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
                    'hero_title_primary' => 'Excelência imobiliária.',
                    'hero_title_secondary' => 'Valor que permanece.',
                    'hero_subtitle' => 'Há mais de 12 anos entregando curadoria, confiança e sofisticação nos melhores endereços.',
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
                        'subtitle' => 'Profissionais apaixonados por encontrar os melhores endereços para cada cliente',
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
            'hero_title_primary' => 'Excelência imobiliária.',
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
                'author' => 'Equipe Meteorikah',
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
            'whatsapp_number' => ['nullable', 'string', 'max:100'],
            'whatsapp_message' => ['nullable', 'string', 'max:500'],
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

        if (!empty($validated['whatsapp_number']) && empty($validated['whatsapp'] ?? null)) {
            $validated['whatsapp'] = $validated['whatsapp_number'];
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
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
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

        if ($request->hasFile('profile_photo')) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $payload['profile_photo_path'] = Storage::disk('public')->putFile("profiles/{$user->id}", $request->file('profile_photo'));
        }

        $user->update($payload);

        return Redirect::route('admin.users')
            ->with('success', 'Usuário atualizado com sucesso.');
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
            'instagram_enabled' => ['nullable', 'boolean'],
            'instagram_username' => ['nullable', 'string', 'max:100'],
            'instagram_user_id' => ['nullable', 'string', 'max:100'],
            'instagram_access_token' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['chave' => $key],
                ['valor' => $key === 'instagram_enabled' ? ((bool) $value ? '1' : '0') : (string) ($value ?? '')]
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

    public function condominiums(): Response
    {
        $items = Condominium::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/Condominiums', [
            'items' => $items,
        ]);
    }

    public function storeCondominium(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], Condominium::class);

        Condominium::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return Redirect::route('admin.condominiums');
    }

    public function updateCondominium(Request $request, Condominium $condominium)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], Condominium::class, $condominium->id);

        $condominium->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? $condominium->is_active,
            'sort_order' => $validated['sort_order'] ?? $condominium->sort_order,
        ]);

        return Redirect::route('admin.condominiums');
    }

    public function destroyCondominium(Condominium $condominium)
    {
        $condominium->delete();

        return Redirect::route('admin.condominiums');
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
            'cover' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], SpecialCategory::class);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = Storage::disk('public')->putFile('special-categories', $request->file('cover'));
        }

        SpecialCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'cover_path' => $coverPath,
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
            'cover' => ['nullable', 'image', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($validated['name'], SpecialCategory::class, $specialCategory->id);

        $coverPath = $specialCategory->cover_path;
        if ($request->hasFile('cover')) {
            if (!empty($specialCategory->cover_path)) {
                Storage::disk('public')->delete($specialCategory->cover_path);
            }
            $coverPath = Storage::disk('public')->putFile('special-categories', $request->file('cover'));
        }

        $specialCategory->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'cover_path' => $coverPath,
            'is_active' => $validated['is_active'] ?? $specialCategory->is_active,
            'sort_order' => $validated['sort_order'] ?? $specialCategory->sort_order,
        ]);

        return Redirect::route('admin.special-categories');
    }

    public function destroySpecialCategory(SpecialCategory $specialCategory)
    {
        if (!empty($specialCategory->cover_path)) {
            Storage::disk('public')->delete($specialCategory->cover_path);
        }

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

    private function propertyPhotoCardUrl(?PropertyPhoto $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        $candidates = [
            $photo->thumb_medium_url,
            $photo->medium_url,
            $photo->thumb_small_url,
            $this->propertyPhotoArquivoUrl($photo),
            $this->propertyPhotoRenderableOriginalUrl($photo),
        ];

        foreach ($candidates as $candidate) {
            if (!$this->photoUrlUsesTemporaryPath($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function photoUrlUsesTemporaryPath(?string $url): bool
    {
        $normalized = (string) ($url ?? '');

        return $normalized === ''
            || str_contains($normalized, '/storage/tmp/property-images/')
            || str_contains($normalized, '/media/tmp/property-images/')
            || str_contains($normalized, '/storage/property-uploads/originals/')
            || str_contains($normalized, '/media/property-uploads/originals/');
    }

    private function propertyPhotoPreviewUrl(PropertyPhoto $photo): ?string
    {
        foreach ([
            $photo->thumb_small_url,
            $photo->medium_url,
            $this->propertyPhotoArquivoUrl($photo),
            $this->propertyPhotoRenderableOriginalUrl($photo),
        ] as $candidate) {
            if (!$this->photoUrlUsesTemporaryPath($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function propertyPhotoArquivoUrl(PropertyPhoto $photo): ?string
    {
        if (!$this->propertyPhotoPathIsRenderable($photo->arquivo, $photo->mime_type, $photo->source_mime_type)) {
            return null;
        }

        return $this->publicMediaUrl($photo->arquivo);
    }

    private function propertyPhotoRenderableOriginalUrl(PropertyPhoto $photo): ?string
    {
        if (!$this->propertyPhotoPathIsRenderable($photo->original_path, $photo->source_mime_type, $photo->mime_type)) {
            return null;
        }

        return $this->publicMediaUrl($photo->original_path);
    }

    private function propertyPhotoPathIsRenderable(?string $path, ?string ...$mimeCandidates): bool
    {
        $normalizedPath = trim((string) ($path ?? ''), '/');

        if ($normalizedPath === '') {
            return false;
        }

        foreach ($mimeCandidates as $mimeCandidate) {
            if ($this->isBrowserRenderableImageMime($mimeCandidate)) {
                return true;
            }
        }

        $extension = strtolower((string) pathinfo($normalizedPath, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    private function isBrowserRenderableImageMime(?string $mimeType): bool
    {
        return in_array(strtolower(trim((string) ($mimeType ?? ''))), [
            'image/jpeg',
            'image/jpg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/webp',
            'image/gif',
        ], true);
    }

    private function publicMediaUrl(?string $path): ?string
    {
        $normalized = trim((string) ($path ?? ''), '/');

        if ($normalized === '') {
            return null;
        }

        return url('/media/' . $normalized);
    }
}
