<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Condominium;
use App\Models\Property;
use App\Models\BusinessType;
use App\Models\PropertyPhoto;
use App\Models\PropertyType;
use App\Models\SpecialCategory;
use App\Models\Setting;
use App\Models\Page;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HomeController extends Controller
{
    public function index(): Response
    {
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
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
            ]
        );

        $baseQuery = Property::with(['photos', 'businessType', 'condominium'])
            ->where('ativo', true)
            ->orderByDesc('created_at');

        $selecaoEspecial = (clone $baseQuery)
            ->where('show_in_home_selecao_especial', true)
            ->limit(12)
            ->get()
            ->map(fn (Property $property) => $this->serializePropertyCard($property))
            ->values();

        $maisProcurados = (clone $baseQuery)
            ->where('show_in_home_mais_procurados', true)
            ->limit(12)
            ->get()
            ->map(fn (Property $property) => $this->serializePropertyCard($property))
            ->values();

        $vistoRecentemente = (clone $baseQuery)
            ->where('show_in_home_visto_recentemente', true)
            ->limit(12)
            ->get()
            ->map(fn (Property $property) => $this->serializePropertyCard($property))
            ->values();

        $settings = Setting::query()->pluck('valor', 'chave');
        $instagramFeed = [];
        $instagramEnabled = !array_key_exists('instagram_enabled', $settings->all())
            || filter_var($settings['instagram_enabled'], FILTER_VALIDATE_BOOLEAN);

        if ($instagramEnabled && !empty($settings['instagram_feed_json'])) {
            $decoded = json_decode($settings['instagram_feed_json'], true);
            if (is_array($decoded)) {
                $instagramFeed = $decoded;
            }
        }

        $businessTypes = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $propertyTypeGroups = PropertyType::query()
            ->orderBy('nome_tipo')
            ->orderBy('nome_subtipo')
            ->get()
            ->groupBy('nome_tipo')
            ->map(fn ($items) => $items->pluck('nome_subtipo')->filter()->values());

        $specialCategories = SpecialCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SpecialCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'cover_url' => $category->cover_url,
                'url' => '/imoveis?special_category_ids[]=' . $category->id,
            ])
            ->values();

        $condominiums = Condominium::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Home', [
            'homePage' => $homePage,
            'selecaoEspecial' => $selecaoEspecial,
            'maisProcurados' => $maisProcurados,
            'vistoRecentemente' => $vistoRecentemente,
            'instagramFeed' => $instagramFeed,
            'instagramEnabled' => $instagramEnabled,
            'instagramUsername' => $settings['instagram_username'] ?? null,
            'instagramUrl' => $settings['instagram_url'] ?? null,
            'businessTypes' => $businessTypes,
            'propertyTypeGroups' => $propertyTypeGroups,
            'specialCategories' => $specialCategories,
            'condominiums' => $condominiums,
        ]);
    }

    public function properties(): Response
    {
        return $this->propertiesWithFilters(request());
    }

    public function propertiesWithFilters(Request $request): Response
    {
        $businessTypes = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values();

        $propertyTypeGroups = PropertyType::query()
            ->orderBy('nome_tipo')
            ->pluck('nome_tipo')
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($name) => ['value' => $name, 'label' => $name]);

        $specialCategories = SpecialCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values();

        $condominiums = Condominium::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values();

        $filters = [
            'q' => $request->string('q')->toString(),
            'business_type_id' => $request->input('business_type_id'),
            'condominium_id' => $request->input('condominium_id'),
            'property_type' => $request->string('property_type')->toString(),
            'special_category_ids' => $request->input('special_category_ids', []),
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'bedrooms_min' => $request->input('bedrooms_min'),
            'suites_min' => $request->input('suites_min'),
            'bathrooms_min' => $request->input('bathrooms_min'),
            'garages_min' => $request->input('garages_min'),
            'area_min' => $request->input('area_min'),
            'area_max' => $request->input('area_max'),
            'lot_area_min' => $request->input('lot_area_min'),
            'lot_area_max' => $request->input('lot_area_max'),
            'sort' => $request->string('sort')->toString(),
        ];

        $query = Property::query()
            ->with(['photos', 'businessType', 'propertyType', 'specialCategories', 'condominium'])
            ->where('ativo', true);

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub
                    ->where('titulo', 'like', '%' . $q . '%')
                    ->orWhere('codigo_referencia', 'like', '%' . $q . '%')
                    ->orWhere('codigo_anuncio', 'like', '%' . $q . '%')
                    ->orWhere('endereco', 'like', '%' . $q . '%')
                    ->orWhere('bairro', 'like', '%' . $q . '%')
                    ->orWhere('cidade', 'like', '%' . $q . '%');
            });
        }

        $businessTypeId = (int) ($filters['business_type_id'] ?? 0);
        if ($businessTypeId > 0) {
            $selectedBusinessType = $businessTypes->firstWhere('id', $businessTypeId);
            $this->applyBusinessTypeFilter($query, $selectedBusinessType);
        }

        $condominiumId = (int) ($filters['condominium_id'] ?? 0);
        if ($condominiumId > 0) {
            $query->where('condominium_id', $condominiumId);
        }

        $propertyType = trim((string) ($filters['property_type'] ?? ''));
        if ($propertyType !== '') {
            $query->whereHas('propertyType', fn ($sub) => $sub->where('nome_tipo', $propertyType));
        }

        $specialCategoryIds = collect($filters['special_category_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        if (count($specialCategoryIds) > 0) {
            $query->whereHas('specialCategories', fn ($sub) => $sub->whereIn('special_categories.id', $specialCategoryIds));
        }

        $priceMin = $this->parseBrlCurrencyNullable($filters['price_min'] ?? null);
        if ($priceMin !== null) {
            $selectedBusinessType = $businessTypeId > 0 ? $businessTypes->firstWhere('id', $businessTypeId) : null;
            $this->applyMinPriceFilter($query, $priceMin, $selectedBusinessType);
        }

        $priceMax = $this->parseBrlCurrencyNullable($filters['price_max'] ?? null);
        if ($priceMax !== null) {
            $selectedBusinessType = $businessTypeId > 0 ? $businessTypes->firstWhere('id', $businessTypeId) : null;
            $this->applyMaxPriceFilter($query, $priceMax, $selectedBusinessType);
        }

        $bedroomsMin = $this->parseIntNullable($filters['bedrooms_min'] ?? null);
        if ($bedroomsMin !== null) {
            $query->where('quartos', '>=', $bedroomsMin);
        }

        $suitesMin = $this->parseIntNullable($filters['suites_min'] ?? null);
        if ($suitesMin !== null) {
            $query->where('suites', '>=', $suitesMin);
        }

        $bathroomsMin = $this->parseIntNullable($filters['bathrooms_min'] ?? null);
        if ($bathroomsMin !== null) {
            $query->where('banheiros', '>=', $bathroomsMin);
        }

        $garagesMin = $this->parseIntNullable($filters['garages_min'] ?? null);
        if ($garagesMin !== null) {
            $query->where('garagens', '>=', $garagesMin);
        }

        $areaMin = $this->parseFloatNullable($filters['area_min'] ?? null);
        if ($areaMin !== null) {
            $query->where('area_util', '>=', $areaMin);
        }

        $areaMax = $this->parseFloatNullable($filters['area_max'] ?? null);
        if ($areaMax !== null) {
            $query->where('area_util', '<=', $areaMax);
        }

        $lotAreaMin = $this->parseFloatNullable($filters['lot_area_min'] ?? null);
        if ($lotAreaMin !== null) {
            $query->where('area_total', '>=', $lotAreaMin);
        }

        $lotAreaMax = $this->parseFloatNullable($filters['lot_area_max'] ?? null);
        if ($lotAreaMax !== null) {
            $query->where('area_total', '<=', $lotAreaMax);
        }

        $sort = (string) ($filters['sort'] ?? '');
        if ($sort === 'price_asc') {
            $selectedBusinessType = $businessTypeId > 0 ? $businessTypes->firstWhere('id', $businessTypeId) : null;
            $query->orderByRaw($this->priceSortExpression($selectedBusinessType) . ' asc');
        } elseif ($sort === 'price_desc') {
            $selectedBusinessType = $businessTypeId > 0 ? $businessTypes->firstWhere('id', $businessTypeId) : null;
            $query->orderByRaw($this->priceSortExpression($selectedBusinessType) . ' desc');
        } else {
            $query->orderByDesc('created_at');
            $filters['sort'] = 'newest';
        }

        $properties = $query
            ->paginate(18)
            ->withQueryString()
            ->through(fn (Property $property) => $this->serializePropertyCard($property));

        return Inertia::render('Properties', [
            'properties' => $properties,
            'filters' => $filters,
            'businessTypes' => $businessTypes,
            'propertyTypeGroups' => $propertyTypeGroups,
            'specialCategories' => $specialCategories,
            'condominiums' => $condominiums,
        ]);
    }

    public function feedImoveisXml()
    {
        $settings = Setting::query()->pluck('valor', 'chave');

        Setting::updateOrCreate(
            ['chave' => 'feed_imoveis_last_generated_at'],
            ['valor' => now()->toISOString()]
        );

        $properties = Property::query()
            ->with(['photos', 'businessType', 'propertyType'])
            ->where('ativo', true)
            ->orderByDesc('updated_at')
            ->get();

        $dataModificacao = (int) ($properties
            ->map(function (Property $p) {
                if (!empty($p->data_modificacao_xml)) {
                    return (int) $p->data_modificacao_xml;
                }
                return (int) ($p->updated_at?->getTimestampMs() ?? now()->getTimestampMs());
            })
            ->max() ?? now()->getTimestampMs());

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= "<OpenNavent>\n";
        $xml .= "  <dataModificacao>" . $this->cdata($dataModificacao) . "</dataModificacao>\n";
        $xml .= "  <Imoveis>\n";

        foreach ($properties as $property) {
            $operacao = $this->mapOperacaoForXml($property);

            $tipo = $property->propertyType?->nome_tipo;
            $subTipo = $property->propertyType?->nome_subtipo;
            $idTipo = $property->propertyType?->id_tipo_xml;
            $idSubTipo = $property->propertyType?->id_subtipo_xml;

            $endereco = trim(implode(', ', array_filter([
                trim((string) $property->endereco),
                trim((string) $property->numero),
                trim((string) $property->bairro),
                trim((string) $property->cidade),
                trim((string) $property->estado),
                trim((string) $property->cep),
            ])));

            $descricao = (string) ($property->descricao ?? '');
            $preco = number_format((float) ($property->valor ?? 0), 2, '.', '');
            $moeda = (string) ($property->moeda ?? 'BRL');

            $photoUrls = $property->photos
                ?->sortBy('ordem')
                ->map(fn (PropertyPhoto $photo) => $this->propertyPhotoXmlUrl($photo))
                ->filter()
                ->values()
                ->all() ?? [];

            $publicador = trim(implode(' | ', array_filter([
                $settings['nome_empresa'] ?? null,
                $settings['telefone'] ?? null,
                $settings['email_contato'] ?? null,
            ])));

            $xml .= "    <Imovel>\n";
            $xml .= "      <codigoAnuncio>" . $this->cdata($this->normalizeCodigoAnuncio($property->codigo_anuncio, (int) $property->id)) . "</codigoAnuncio>\n";
            if (!empty($property->codigo_referencia)) {
                $xml .= "      <codigoReferencia>" . $this->cdata($property->codigo_referencia) . "</codigoReferencia>\n";
            }

            $xml .= "      <tipoPropriedade>\n";
            if (!empty($idTipo)) {
                $xml .= "        <idTipo>" . $this->cdata($idTipo) . "</idTipo>\n";
            }
            if (!empty($tipo)) {
                $xml .= "        <tipo>" . $this->cdata($tipo) . "</tipo>\n";
            }
            if (!empty($idSubTipo)) {
                $xml .= "        <idSubTipo>" . $this->cdata($idSubTipo) . "</idSubTipo>\n";
            }
            if (!empty($subTipo)) {
                $xml .= "        <subTipo>" . $this->cdata($subTipo) . "</subTipo>\n";
            }
            $xml .= "      </tipoPropriedade>\n";

            $xml .= "      <Operacao>" . $this->cdata($operacao) . "</Operacao>\n";
            $xml .= "      <Endereco>" . $this->cdata($endereco) . "</Endereco>\n";
            $xml .= "      <Preco>" . $this->cdata($preco) . "</Preco>\n";
            $xml .= "      <Moeda>" . $this->cdata($moeda) . "</Moeda>\n";
            $xml .= "      <Descricao>" . $this->cdata($descricao) . "</Descricao>\n";

            if (!is_null($property->quartos)) {
                $xml .= "      <Quartos>" . $this->cdata((int) $property->quartos) . "</Quartos>\n";
            }
            if (!is_null($property->banheiros)) {
                $xml .= "      <Banheiros>" . $this->cdata((int) $property->banheiros) . "</Banheiros>\n";
            }
            if (!is_null($property->garagens)) {
                $xml .= "      <Vagas>" . $this->cdata((int) $property->garagens) . "</Vagas>\n";
            }
            if (!is_null($property->area_util)) {
                $xml .= "      <AreaUtil>" . $this->cdata(number_format((float) $property->area_util, 2, '.', '')) . "</AreaUtil>\n";
            }
            if (!is_null($property->area_total)) {
                $xml .= "      <AreaTotal>" . $this->cdata(number_format((float) $property->area_total, 2, '.', '')) . "</AreaTotal>\n";
            }
            if (!is_null($property->latitud)) {
                $xml .= "      <Latitud>" . $this->cdata((string) $property->latitud) . "</Latitud>\n";
            }
            if (!is_null($property->longitud)) {
                $xml .= "      <Longitud>" . $this->cdata((string) $property->longitud) . "</Longitud>\n";
            }

            if (count($photoUrls) > 0) {
                $xml .= "      <Imagens>\n";
                foreach ($photoUrls as $u) {
                    $xml .= "        <Imagem>" . $this->cdata($u) . "</Imagem>\n";
                }
                $xml .= "      </Imagens>\n";
            }

            if ($publicador !== '') {
                $xml .= "      <Publicador>" . $this->cdata($publicador) . "</Publicador>\n";
            }

            $xml .= "    </Imovel>\n";
        }

        $xml .= "  </Imoveis>\n";
        $xml .= "</OpenNavent>\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function normalizeCodigoAnuncio(?string $codigo, int $fallbackId): string
    {
        $raw = strtoupper(trim((string) ($codigo ?? '')));
        $clean = preg_replace('/[^A-Z0-9]/', '', $raw) ?? '';

        if (str_starts_with($clean, 'IMB') && strlen($clean) > 8) {
            $clean = substr($clean, 3);
        }

        if (strlen($clean) >= 8) {
            return substr($clean, 0, 8);
        }

        return str_pad((string) $fallbackId, 8, '0', STR_PAD_LEFT);
    }

    public function sell(): Response
    {
        return Inertia::render('Sell');
    }

    public function sendSell(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'tipo_imovel' => ['nullable', 'string', 'max:255'],
            'quartos' => ['nullable', 'string', 'max:255'],
            'banheiros' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string'],
        ]);

        $parts = [];
        if (!empty($validated['tipo_imovel'])) $parts[] = 'Tipo: ' . $validated['tipo_imovel'];
        if (!empty($validated['quartos'])) $parts[] = 'Quartos: ' . $validated['quartos'];
        if (!empty($validated['banheiros'])) $parts[] = 'Banheiros: ' . $validated['banheiros'];
        if (!empty($validated['area'])) $parts[] = 'Área: ' . $validated['area'];
        if (!empty($validated['mensagem'])) $parts[] = 'Mensagem: ' . $validated['mensagem'];
        $mensagem = count($parts) ? implode("\n", $parts) : null;

        Lead::create([
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? '',
            'mensagem' => $mensagem,
            'origem' => 'Site - Venda seu Imóvel',
            'categoria' => 'venda-seu-imovel',
            'status' => 'Novo Lead',
        ]);

        return Redirect::back();
    }

    public function showProperty(Request $request, string $slug): Response
    {
        $propertyModel = Property::with(['propertyType', 'businessType', 'photos', 'condominium'])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $sessionId = (string) $request->session()->getId();
        if ($sessionId !== '') {
            try {
                $recent = DB::table('property_views')
                    ->where('property_id', $propertyModel->id)
                    ->where('session_id', $sessionId)
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();

                if (!$recent) {
                    DB::table('property_views')->insert([
                        'property_id' => $propertyModel->id,
                        'session_id' => $sessionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable) {
            }
        }

        $photos = $propertyModel->photos
            ->sortBy('ordem')
            ->map(fn (PropertyPhoto $p) => $this->serializeResponsivePhoto($p))
            ->filter(fn ($p) => !empty($p['full']) || !empty($p['src']))
            ->values()
            ->all();

        $property = [
            'id' => $propertyModel->id,
            'slug' => $propertyModel->slug,
            'title' => $propertyModel->titulo,
            'address' => trim($propertyModel->endereco . ' - ' . $propertyModel->bairro . ', ' . $propertyModel->cidade . '/' . $propertyModel->estado),
            'price' => $this->primaryPublicPriceValue($propertyModel),
            'prices' => $this->propertyPublicPrices($propertyModel),
            'type' => $propertyModel->primaryBusinessLabel(),
            'businessLabels' => $propertyModel->businessLabels(),
            'condominiumName' => $propertyModel->condominium?->name,
            'propertyType' => $propertyModel->propertyType?->nome_tipo ?? '',
            'code' => $propertyModel->codigo_referencia ?: $propertyModel->codigo_anuncio,
            'isExclusive' => (bool) $propertyModel->is_exclusive,
            'bedrooms' => (int) ($propertyModel->quartos ?? 0),
            'suites' => (int) ($propertyModel->suites ?? 0),
            'bathrooms' => (int) ($propertyModel->banheiros ?? 0),
            'lavabos' => (int) ($propertyModel->lavabos ?? 0),
            'garages' => (int) ($propertyModel->garagens ?? 0),
            'floor' => (int) ($propertyModel->andar ?? 0),
            'areaTotal' => (float) ($propertyModel->area_total ?? 0),
            'areaBuilt' => (float) ($propertyModel->area_construida ?? $propertyModel->area_util ?? 0),
            'valorCondominio' => (float) ($propertyModel->valor_condominio ?? $propertyModel->condominio ?? 0),
            'valorIptu' => (float) ($propertyModel->valor_iptu ?? $propertyModel->iptu ?? 0),
            'aceitaPermuta' => (bool) $propertyModel->aceita_permuta,
            'aceitaFinanciamento' => (bool) $propertyModel->aceita_financiamento,
            'mobiliado' => (bool) $propertyModel->mobiliado,
            'anoConstrucao' => $propertyModel->ano_construcao ? (int) $propertyModel->ano_construcao : null,
            'posicaoSolar' => $propertyModel->posicao_solar ?: null,
            'description' => $propertyModel->descricao,
            'photos' => $photos,
        ];

        return Inertia::render('PropertyShow', [
            'property' => $property,
        ]);
    }

    public function instagramMedia(string $mediaId)
    {
        $settings = Setting::query()->pluck('valor', 'chave');
        $feed = [];

        if (!empty($settings['instagram_feed_json'])) {
            $decoded = json_decode($settings['instagram_feed_json'], true);
            if (is_array($decoded)) {
                $feed = $decoded;
            }
        }

        $media = collect($feed)->firstWhere('id', $mediaId);
        $mediaUrl = $media['thumbnail_url'] ?? $media['media_url'] ?? null;

        if (empty($mediaUrl)) {
            abort(404);
        }

        try {
            $content = file_get_contents($mediaUrl);
        } catch (\Throwable) {
            abort(404);
        }

        if ($content === false) {
            abort(404);
        }

        return response($content, 200)->header('Content-Type', 'image/jpeg');
    }

    public function storageMedia(string $path): SymfonyResponse
    {
        $normalizedPath = trim($path, '/');
        $resolvedMedia = null;
        $storagePath = null;
        $mime = null;
        $response = null;

        try {
            if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
                abort(404);
            }

            if (str_starts_with($normalizedPath, 'tmp/property-images/')
                || str_starts_with($normalizedPath, 'property-uploads/originals/')) {
                abort(404);
            }

            $resolvedMedia = $this->resolvePublicMediaAsset($normalizedPath);
            $exists = $resolvedMedia !== null;

            if (env('TRAE_DEBUG_FRONT_IMAGES_MISSING')) {
                // #region debug-point C:storage-media-request
                rescue(function () use ($normalizedPath, $resolvedMedia, $exists): void {
                    Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                        'sessionId' => 'front-images-missing',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'C',
                        'location' => 'app/Http/Controllers/HomeController.php:storageMedia',
                        'msg' => '[DEBUG] Storage media requested',
                        'data' => [
                            'path' => $normalizedPath,
                            'exists' => $exists,
                            'resolved_disk' => $resolvedMedia['disk'] ?? null,
                            'resolved_full_path' => $resolvedMedia['full_path'] ?? null,
                        ],
                        'ts' => (int) round(microtime(true) * 1000),
                    ]);
                }, report: false);
                // #endregion
            }

            if (env('TRAE_DEBUG_MEDIA_ROUTE_500')) {
                Log::info('Media route request entered.', [
                    'requested_path' => $path,
                    'normalized_path' => $normalizedPath,
                    'resolved_media' => $resolvedMedia,
                    'exists' => $exists,
                    'request_url' => request()->fullUrl(),
                ]);
            }

            if (!$exists) {
                abort(404);
            }

            $storagePath = $resolvedMedia['path'];
            $mime = $resolvedMedia['mime'];

            if (empty($mime)) {
                $ext = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'ico' => 'image/x-icon',
                    default => 'application/octet-stream',
                };
            }

            $response = Storage::disk($resolvedMedia['disk'])->response($storagePath, null, [
                'Content-Type' => $mime,
                'Access-Control-Allow-Origin' => '*',
                'Cross-Origin-Resource-Policy' => 'cross-origin',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);

            if (env('TRAE_DEBUG_MEDIA_ROUTE_500')) {
                Log::info('Media route response prepared.', [
                    'normalized_path' => $normalizedPath,
                    'disk' => $resolvedMedia['disk'] ?? null,
                    'storage_path' => $storagePath,
                    'full_path' => $resolvedMedia['full_path'] ?? null,
                    'mime' => $mime,
                    'response_class' => get_class($response),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                && $e->getStatusCode() === 404) {
                throw $e;
            }

            Log::error('Media route delivery failed.', [
                'requested_path' => $path,
                'normalized_path' => $normalizedPath,
                'resolved_media' => $resolvedMedia,
                'disk' => $resolvedMedia['disk'] ?? null,
                'storage_path' => $storagePath,
                'full_path' => $resolvedMedia['full_path'] ?? null,
                'mime' => $mime,
                'response_class' => is_object($response) ? get_class($response) : null,
                'exception_class' => $e::class,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }

    private function resolvePublicMediaAsset(string $path): ?array
    {
        $candidateDisks = array_values(array_unique(array_filter([
            (string) config('image_uploads.final_disk', 'public'),
            (string) config('image_uploads.original_disk', 'public'),
            'public',
        ])));

        foreach ($candidateDisks as $diskName) {
            $disk = Storage::disk($diskName);

            if (!$disk->exists($path)) {
                continue;
            }

            $fullPath = $disk->path($path);
            $mime = rescue(
                fn (): ?string => $disk->mimeType($path) ?: File::mimeType($fullPath),
                null,
                false
            );

            return [
                'disk' => $diskName,
                'path' => $path,
                'full_path' => $fullPath,
                'mime' => $mime,
            ];
        }

        return null;
    }

    private function parseBrlCurrencyNullable(mixed $input): ?float
    {
        if ($input === null) {
            return null;
        }

        $str = trim((string) $input);
        if ($str === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $str);
        if ($digits === null || $digits === '') {
            return null;
        }

        return ((float) $digits) / 100;
    }

    private function parseIntNullable(mixed $input): ?int
    {
        if ($input === null) {
            return null;
        }

        $str = trim((string) $input);
        if ($str === '') {
            return null;
        }

        if (!is_numeric($str)) {
            return null;
        }

        return (int) $str;
    }

    private function parseFloatNullable(mixed $input): ?float
    {
        if ($input === null) {
            return null;
        }

        $str = trim((string) $input);
        if ($str === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $str);
        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function cdata(mixed $value): string
    {
        $s = (string) ($value ?? '');
        $s = str_replace(']]>', ']]]]><![CDATA[>', $s);
        return '<![CDATA[' . $s . ']]>';
    }

    private function applyBusinessTypeFilter($query, ?BusinessType $businessType): void
    {
        $name = trim((string) ($businessType?->name ?? ''));

        if ($name === 'Comprar') {
            $query->where(function ($sub): void {
                $sub->where('aceita_venda', true)
                    ->orWhere('operacao', 'Venda');
            });
            return;
        }

        if ($name === 'Alugar') {
            $query->where(function ($sub): void {
                $sub->where('aceita_locacao', true)
                    ->orWhere('operacao', 'Aluguel');
            });
            return;
        }

        if ($name === 'Temporada') {
            $query->where(function ($sub): void {
                $sub->where('aceita_temporada', true)
                    ->orWhere('operacao', 'Temporada');
            });
        }
    }

    private function applyMinPriceFilter($query, float $priceMin, ?BusinessType $businessType): void
    {
        $name = trim((string) ($businessType?->name ?? ''));

        if ($name === 'Comprar') {
            $query->where(function ($sub) use ($priceMin): void {
                $sub->where('valor_venda', '>=', $priceMin)
                    ->orWhere(function ($legacy) use ($priceMin): void {
                        $legacy->whereNull('valor_venda')
                            ->where('valor', '>=', $priceMin)
                            ->where('operacao', 'Venda');
                    });
            });
            return;
        }

        if ($name === 'Alugar') {
            $query->where(function ($sub) use ($priceMin): void {
                $sub->where('valor_locacao', '>=', $priceMin)
                    ->orWhere(function ($legacy) use ($priceMin): void {
                        $legacy->whereNull('valor_locacao')
                            ->where('valor', '>=', $priceMin)
                            ->where('operacao', 'Aluguel');
                    });
            });
            return;
        }

        $query->where(function ($sub) use ($priceMin): void {
            $sub->where('valor_venda', '>=', $priceMin)
                ->orWhere('valor_locacao', '>=', $priceMin)
                ->orWhere('valor', '>=', $priceMin);
        });
    }

    private function applyMaxPriceFilter($query, float $priceMax, ?BusinessType $businessType): void
    {
        $name = trim((string) ($businessType?->name ?? ''));

        if ($name === 'Comprar') {
            $query->where(function ($sub) use ($priceMax): void {
                $sub->where('valor_venda', '<=', $priceMax)
                    ->orWhere(function ($legacy) use ($priceMax): void {
                        $legacy->whereNull('valor_venda')
                            ->where('valor', '<=', $priceMax)
                            ->where('operacao', 'Venda');
                    });
            });
            return;
        }

        if ($name === 'Alugar') {
            $query->where(function ($sub) use ($priceMax): void {
                $sub->where('valor_locacao', '<=', $priceMax)
                    ->orWhere(function ($legacy) use ($priceMax): void {
                        $legacy->whereNull('valor_locacao')
                            ->where('valor', '<=', $priceMax)
                            ->where('operacao', 'Aluguel');
                    });
            });
            return;
        }

        $query->where(function ($sub) use ($priceMax): void {
            $sub->where('valor_venda', '<=', $priceMax)
                ->orWhere('valor_locacao', '<=', $priceMax)
                ->orWhere('valor', '<=', $priceMax);
        });
    }

    private function priceSortExpression(?BusinessType $businessType): string
    {
        $name = trim((string) ($businessType?->name ?? ''));

        if ($name === 'Comprar') {
            return 'COALESCE(NULLIF(valor_venda, 0), NULLIF(valor, 0), 0)';
        }

        if ($name === 'Alugar') {
            return 'COALESCE(NULLIF(valor_locacao, 0), NULLIF(valor, 0), 0)';
        }

        return 'COALESCE(NULLIF(valor_venda, 0), NULLIF(valor_locacao, 0), NULLIF(valor, 0), 0)';
    }

    private function propertyPublicPrices(Property $property): array
    {
        return $property->publicPrices()->values()->all();
    }

    private function primaryPublicPriceValue(Property $property): float
    {
        return (float) ($property->publicPrices()->first()['value'] ?? 0);
    }

    private function serializePropertyCard(Property $property): array
    {
        $sortedPhotos = $property->photos->sortBy('ordem');
        $photo = $sortedPhotos->firstWhere('principal', true) ?? $sortedPhotos->first();
        $photoUrls = $sortedPhotos
            ->map(fn (PropertyPhoto $item) => $this->serializeResponsivePhoto($item))
            ->filter()
            ->values()
            ->all();

        if (env('TRAE_DEBUG_FRONT_IMAGES_MISSING')) {
            // #region debug-point A:serialize-property-card
            rescue(function () use ($property, $photo, $photoUrls): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'front-images-missing',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/HomeController.php:serializePropertyCard',
                    'msg' => '[DEBUG] Property card photo payload prepared',
                    'data' => [
                        'property_id' => $property->id,
                        'selected_photo_id' => $photo?->id,
                        'photos_count' => count($photoUrls),
                        'selected_src' => $photoUrls[0]['src'] ?? null,
                        'selected_full' => $photoUrls[0]['full'] ?? null,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        return [
            'id' => $property->id,
            'slug' => $property->slug,
            'url' => '/imoveis/' . $property->slug,
            'code' => $property->codigo_referencia ?: $property->codigo_anuncio,
            'title' => trim((string) $property->titulo),
            'address' => $this->buildPropertyCardAddress($property),
            'location' => $this->buildPropertyCardLocation($property),
            'price' => $this->primaryPublicPriceValue($property),
            'prices' => $this->propertyPublicPrices($property),
            'bedrooms' => $this->propertyCardInteger($property->quartos),
            'suites' => $this->propertyCardInteger($property->suites),
            'bathrooms' => $this->propertyCardInteger($property->banheiros),
            'garages' => $this->propertyCardInteger($property->garagens),
            'area' => $this->propertyCardDecimal($property->area_construida ?? $property->area_util),
            'lotArea' => $this->propertyCardDecimal($property->area_total),
            'type' => $property->primaryBusinessLabel(),
            'businessLabels' => $property->businessLabels(),
            'condominium' => $property->condominium?->name,
            'photo' => $this->serializeResponsivePhoto($photo),
            'photos' => $photoUrls,
        ];
    }

    private function buildPropertyCardLocation(Property $property): string
    {
        $parts = array_values(array_filter([
            $this->sanitizePropertyCardText($property->bairro),
            $this->sanitizePropertyCardText($property->cidade),
        ]));

        if ($parts !== []) {
            return implode(' - ', $parts);
        }

        return $this->buildPropertyCardAddress($property);
    }

    private function buildPropertyCardAddress(Property $property): string
    {
        $line = array_values(array_filter([
            $this->sanitizePropertyCardText($property->endereco),
            $this->sanitizePropertyCardText($property->bairro),
        ]));

        $city = array_values(array_filter([
            $this->sanitizePropertyCardText($property->cidade),
            $this->sanitizePropertyCardText($property->estado),
        ]));

        $parts = [];

        if ($line !== []) {
            $parts[] = implode(' - ', $line);
        }

        if ($city !== []) {
            $parts[] = implode('/', $city);
        }

        return implode(', ', $parts);
    }

    private function sanitizePropertyCardText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : null;
    }

    private function propertyCardInteger(mixed $value): ?int
    {
        $number = (int) $value;

        return $number > 0 ? $number : null;
    }

    private function propertyCardDecimal(mixed $value): ?float
    {
        $number = (float) $value;

        return $number > 0 ? $number : null;
    }

    private function mapOperacaoForXml(Property $property): string
    {
        if ($property->aceita_venda) {
            return 'Venda';
        }
        if ($property->aceita_locacao) {
            return 'Aluguel';
        }
        if ($property->aceita_temporada) {
            return 'Temporada';
        }
        if (!empty($property->operacao)) {
            return (string) $property->operacao;
        }
        return 'Venda';
    }

    private function propertyPhotoCardUrl(?PropertyPhoto $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        $resolvedUrl = $this->propertyPhotoValidPublicUrl($photo->thumb_small_url)
            ?: $this->propertyPhotoValidPublicUrl($photo->thumb_medium_url)
            ?: $this->propertyPhotoValidPublicUrl($photo->medium_url)
            ?: $this->propertyPhotoStableUrl($photo)
            ?: $this->propertyPhotoRenderableOriginalUrl($photo)
            ?: null;

        return $resolvedUrl;
    }

    private function serializeResponsivePhoto(?PropertyPhoto $photo): ?array
    {
        if (!$photo) {
            return null;
        }

        $stableUrl = $this->propertyPhotoStableUrl($photo);
        $renderableOriginalUrl = $this->propertyPhotoRenderableOriginalUrl($photo);
        $thumbSmallUrl = $this->propertyPhotoValidPublicUrl($photo->thumb_small_url);
        $thumbMediumUrl = $this->propertyPhotoValidPublicUrl($photo->thumb_medium_url);
        $thumb = $thumbSmallUrl ?: $thumbMediumUrl ?: $stableUrl ?: $renderableOriginalUrl;
        $medium = $thumbMediumUrl ?: $thumbSmallUrl ?: $stableUrl ?: $renderableOriginalUrl;
        $full = $stableUrl ?: $thumbMediumUrl ?: $thumbSmallUrl ?: $renderableOriginalUrl;

        if (env('TRAE_DEBUG_FRONT_IMAGES_IMAGICK')) {
            // #region debug-point A:serialize-responsive-photo
            rescue(function () use ($photo, $stableUrl, $renderableOriginalUrl, $thumb, $medium, $full): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'front-images-imagick',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/HomeController.php:serializeResponsivePhoto',
                    'msg' => '[DEBUG] Responsive photo URLs resolved',
                    'data' => [
                        'photo_id' => $photo->id,
                        'property_id' => $photo->property_id,
                        'processing_status' => $photo->processing_status,
                        'source_mime_type' => $photo->source_mime_type,
                        'mime_type' => $photo->mime_type,
                        'original_path' => $photo->original_path,
                        'arquivo' => $photo->arquivo,
                        'thumb_small_path' => $photo->thumb_small_path,
                        'thumb_medium_path' => $photo->thumb_medium_path,
                        'stable_url' => $stableUrl,
                        'renderable_original_url' => $renderableOriginalUrl,
                        'thumb' => $thumb,
                        'medium' => $medium,
                        'full' => $full,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        if (env('TRAE_DEBUG_FRONT_GALLERY_TMP_URLS')) {
            // #region debug-point A:serialize-responsive-photo-temporary
            rescue(function () use ($photo, $thumb, $medium, $full): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'front-gallery-tmp-urls',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/HomeController.php:serializeResponsivePhoto',
                    'msg' => '[DEBUG] Public gallery photo payload prepared',
                    'data' => [
                        'photo_id' => $photo->id,
                        'property_id' => $photo->property_id,
                        'thumb_small_url' => $thumbSmallUrl,
                        'thumb_medium_url' => $thumbMediumUrl,
                        'original_url' => $photo->original_url,
                        'url' => $photo->url,
                        'thumb' => $thumb,
                        'medium' => $medium,
                        'full' => $full,
                        'contains_tmp' => collect([
                            $thumbSmallUrl,
                            $thumbMediumUrl,
                            $photo->original_url,
                            $photo->url,
                            $thumb,
                            $medium,
                            $full,
                        ])->filter(fn (?string $value) => is_string($value) && str_contains($value, '/tmp/property-images/'))->isNotEmpty(),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        if (!$thumb && !$medium && !$full) {
            return null;
        }

        $srcset = collect([
            $thumbSmallUrl ? "{$thumbSmallUrl} 600w" : null,
            $thumbMediumUrl ? "{$thumbMediumUrl} 1200w" : null,
            $stableUrl ? "{$stableUrl} 1920w" : null,
        ])->filter()->implode(', ');

        if (env('TRAE_DEBUG_FRONT_GALLERY_TMP_URLS')) {
            // #region debug-point A:serialize-responsive-photo-srcset
            rescue(function () use ($photo, $srcset): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'front-gallery-tmp-urls',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/HomeController.php:serializeResponsivePhoto:srcset',
                    'msg' => '[DEBUG] Public gallery srcset prepared',
                    'data' => [
                        'photo_id' => $photo->id,
                        'property_id' => $photo->property_id,
                        'srcset' => $srcset,
                        'contains_tmp' => str_contains($srcset, '/tmp/property-images/'),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        return [
            'src' => $thumb ?: $medium ?: $full,
            'thumb' => $thumb ?: $medium ?: $full,
            'medium' => $medium ?: $thumb ?: $full,
            'full' => $full ?: $medium ?: $thumb,
            'srcset' => $srcset !== '' ? $srcset : null,
            'sizes' => '(max-width: 768px) 100vw, (max-width: 1280px) 50vw, 600px',
            'full_sizes' => '(max-width: 768px) 100vw, 1200px',
        ];
    }

    private function propertyPhotoStableUrl(PropertyPhoto $photo): ?string
    {
        $arquivoUrl = $this->propertyPhotoExistingPublicAssetUrl($photo->arquivo);

        if ($arquivoUrl && $this->propertyPhotoPathIsRenderable($photo->arquivo, $photo->mime_type, $photo->source_mime_type)) {
            return $arquivoUrl;
        }

        return $this->propertyPhotoLegacyExternalUrl($photo);
    }

    private function propertyPhotoRenderableOriginalUrl(PropertyPhoto $photo): ?string
    {
        $mimeType = strtolower((string) ($photo->source_mime_type ?: $photo->mime_type ?: ''));

        if (!$this->propertyPhotoPathIsRenderable($photo->original_path, $photo->source_mime_type, $photo->mime_type)) {
            if (env('TRAE_DEBUG_FRONT_IMAGES_IMAGICK')) {
                // #region debug-point A:renderable-original-rejected
                rescue(function () use ($photo, $mimeType): void {
                    Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                        'sessionId' => 'front-images-imagick',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'A',
                        'location' => 'app/Http/Controllers/HomeController.php:propertyPhotoRenderableOriginalUrl:rejected',
                        'msg' => '[DEBUG] Original fallback rejected for mime type',
                        'data' => [
                            'photo_id' => $photo->id,
                            'property_id' => $photo->property_id,
                            'processing_status' => $photo->processing_status,
                            'source_mime_type' => $photo->source_mime_type,
                            'mime_type' => $photo->mime_type,
                            'resolved_mime_type' => $mimeType,
                            'original_path' => $photo->original_path,
                        ],
                        'ts' => (int) round(microtime(true) * 1000),
                    ]);
                }, report: false);
                // #endregion
            }

            return null;
        }

        $url = $this->propertyPhotoExistingPublicAssetUrl($photo->original_path);

        if (env('TRAE_DEBUG_FRONT_IMAGES_IMAGICK')) {
            // #region debug-point A:renderable-original-accepted
            rescue(function () use ($photo, $mimeType, $url): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'front-images-imagick',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Controllers/HomeController.php:propertyPhotoRenderableOriginalUrl:accepted',
                    'msg' => '[DEBUG] Original fallback accepted',
                    'data' => [
                        'photo_id' => $photo->id,
                        'property_id' => $photo->property_id,
                        'processing_status' => $photo->processing_status,
                        'resolved_mime_type' => $mimeType,
                        'original_path' => $photo->original_path,
                        'url' => $url,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        return $url;
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

    private function propertyPhotoUsesStagingUrl(string $url): bool
    {
        return str_contains($url, '/storage/tmp/property-images/')
            || str_contains($url, '/media/tmp/property-images/')
            || str_contains($url, '/storage/property-uploads/originals/')
            || str_contains($url, '/media/property-uploads/originals/');
    }

    private function propertyPhotoValidPublicUrl(?string $url): ?string
    {
        $normalized = trim((string) ($url ?? ''));

        if ($normalized === '' || $this->propertyPhotoUsesStagingUrl($normalized)) {
            return null;
        }

        $parsedPath = parse_url($normalized, PHP_URL_PATH);

        if (is_string($parsedPath) && str_starts_with($parsedPath, '/media/')) {
            $assetPath = ltrim(substr($parsedPath, strlen('/media/')), '/');

            return $this->propertyPhotoExistingPublicAssetUrl($assetPath);
        }

        if (is_string($parsedPath) && str_starts_with($parsedPath, '/storage/')) {
            return null;
        }

        return $normalized;
    }

    private function propertyPhotoXmlUrl(PropertyPhoto $photo): ?string
    {
        return $this->propertyPhotoExistingPublicAssetUrl($photo->arquivo)
            ?: $this->propertyPhotoRenderableOriginalUrl($photo)
            ?: $this->propertyPhotoLegacyExternalUrl($photo);
    }

    private function propertyPhotoPublicAssetUrl(?string $path): ?string
    {
        $normalized = trim((string) ($path ?? ''), '/');

        if ($normalized === '') {
            return null;
        }

        $resolvedUrl = url('/media/' . $normalized);

        if (env('TRAE_DEBUG_HTTPS_IMAGE_MIXED_CONTENT')) {
            // #region debug-point B:property-photo-public-asset-url
            rescue(function () use ($normalized, $resolvedUrl): void {
                $request = request();

                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'https-image-mixed-content',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'B',
                    'location' => 'app/Http/Controllers/HomeController.php:propertyPhotoPublicAssetUrl',
                    'msg' => '[DEBUG] Public media URL generated for property photo',
                    'data' => [
                        'path' => $normalized,
                        'resolved_url' => $resolvedUrl,
                        'app_url_config' => config('app.url'),
                        'url_root' => url('/'),
                        'request_scheme' => $request->getScheme(),
                        'request_is_secure' => $request->isSecure(),
                        'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
                        'forwarded' => $request->headers->get('forwarded'),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        return $resolvedUrl;
    }

    private function propertyPhotoExistingPublicAssetUrl(?string $path): ?string
    {
        $normalized = trim((string) ($path ?? ''), '/');

        if ($normalized === ''
            || str_starts_with($normalized, 'tmp/property-images/')
            || str_starts_with($normalized, 'property-uploads/originals/')) {
            return null;
        }

        if (!$this->resolvePublicMediaAsset($normalized)) {
            return null;
        }

        return url('/media/' . $normalized);
    }

    private function propertyPhotoLegacyExternalUrl(PropertyPhoto $photo): ?string
    {
        $url = trim((string) ($photo->url ?? ''));

        if ($url === '' || $this->propertyPhotoUsesStagingUrl($url) || str_contains($url, '/storage/')) {
            return null;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);

        if (is_string($parsedPath) && str_starts_with($parsedPath, '/media/')) {
            return $this->propertyPhotoValidPublicUrl($url);
        }

        return $url;
    }
}
