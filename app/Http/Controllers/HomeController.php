<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\BusinessType;
use App\Models\PropertyType;
use App\Models\SpecialCategory;
use App\Models\Setting;
use App\Models\Page;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $mapProperty = function (Property $property): array {
            $sortedPhotos = $property->photos->sortBy('ordem');
            $photo = $sortedPhotos->firstWhere('principal', true) ?? $sortedPhotos->first();
            $photoUrls = $sortedPhotos
                ->pluck('url')
                ->filter()
                ->values()
                ->all();

            return [
                'id' => $property->id,
                'slug' => $property->slug,
                'url' => '/imoveis/' . $property->slug,
                'code' => $property->codigo_referencia ?: $property->codigo_anuncio,
                'title' => $property->titulo,
                'address' => trim($property->endereco . ' - ' . $property->bairro . ', ' . $property->cidade . '/' . $property->estado),
                'location' => trim(($property->bairro ? $property->bairro . ' - ' : '') . $property->cidade),
                'price' => (float) $property->valor,
                'bedrooms' => (int) ($property->quartos ?? 0),
                'bathrooms' => (int) ($property->banheiros ?? 0),
                'area' => (float) ($property->area_util ?? 0),
                'lotArea' => (float) ($property->area_total ?? 0),
                'type' => $property->businessType?->name ?? $property->operacao,
                'photo' => $photo?->url,
                'photos' => $photoUrls,
            ];
        };

        $baseQuery = Property::with(['photos', 'businessType'])
            ->where('ativo', true)
            ->orderByDesc('created_at');

        $selecaoEspecial = (clone $baseQuery)
            ->where('show_in_home_selecao_especial', true)
            ->limit(12)
            ->get()
            ->map($mapProperty)
            ->values();

        $maisProcurados = (clone $baseQuery)
            ->where('show_in_home_mais_procurados', true)
            ->limit(12)
            ->get()
            ->map($mapProperty)
            ->values();

        $vistoRecentemente = (clone $baseQuery)
            ->where('show_in_home_visto_recentemente', true)
            ->limit(12)
            ->get()
            ->map($mapProperty)
            ->values();

        $settings = Setting::query()->pluck('valor', 'chave');
        $instagramFeed = [];

        if (!empty($settings['instagram_feed_json'])) {
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
            ->get(['id', 'name']);

        return Inertia::render('Home', [
            'homePage' => $homePage,
            'selecaoEspecial' => $selecaoEspecial,
            'maisProcurados' => $maisProcurados,
            'vistoRecentemente' => $vistoRecentemente,
            'instagramFeed' => $instagramFeed,
            'instagramUsername' => $settings['instagram_username'] ?? null,
            'instagramUrl' => $settings['instagram_url'] ?? null,
            'businessTypes' => $businessTypes,
            'propertyTypeGroups' => $propertyTypeGroups,
            'specialCategories' => $specialCategories,
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

        $filters = [
            'q' => $request->string('q')->toString(),
            'business_type_id' => $request->input('business_type_id'),
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
            ->with(['photos', 'businessType', 'propertyType', 'specialCategories'])
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
            $query->where('business_type_id', $businessTypeId);
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
            $query->where('valor', '>=', $priceMin);
        }

        $priceMax = $this->parseBrlCurrencyNullable($filters['price_max'] ?? null);
        if ($priceMax !== null) {
            $query->where('valor', '<=', $priceMax);
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
            $query->orderBy('valor');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('valor');
        } else {
            $query->orderByDesc('created_at');
            $filters['sort'] = 'newest';
        }

        $properties = $query
            ->paginate(18)
            ->withQueryString()
            ->through(function (Property $property) {
                $photo = $property->photos
                    ->sortBy('ordem')
                    ->firstWhere('principal', true) ?? $property->photos->sortBy('ordem')->first();
                $photoUrls = $property->photos
                    ->sortBy('ordem')
                    ->pluck('url')
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $property->id,
                    'slug' => $property->slug,
                    'url' => '/imoveis/' . $property->slug,
                    'code' => $property->codigo_referencia ?: $property->codigo_anuncio,
                    'title' => $property->titulo,
                    'address' => trim($property->endereco . ' - ' . $property->bairro . ', ' . $property->cidade . '/' . $property->estado),
                    'location' => trim(($property->bairro ? $property->bairro . ' - ' : '') . $property->cidade),
                    'price' => (float) $property->valor,
                    'bedrooms' => (int) ($property->quartos ?? 0),
                    'suites' => (int) ($property->suites ?? 0),
                    'bathrooms' => (int) ($property->banheiros ?? 0),
                    'garages' => (int) ($property->garagens ?? 0),
                    'area' => (float) ($property->area_util ?? 0),
                    'lotArea' => (float) ($property->area_total ?? 0),
                    'type' => $property->businessType?->name ?? $property->operacao,
                    'photo' => $photo?->url,
                    'photos' => $photoUrls,
                ];
            });

        return Inertia::render('Properties', [
            'properties' => $properties,
            'filters' => $filters,
            'businessTypes' => $businessTypes,
            'propertyTypeGroups' => $propertyTypeGroups,
            'specialCategories' => $specialCategories,
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
                ->map(fn ($p) => $p->url)
                ->filter()
                ->map(fn ($url) => url($url))
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
        $propertyModel = Property::with(['propertyType', 'businessType', 'photos'])
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
            ->map(fn ($p) => $p->url)
            ->values()
            ->all();

        $property = [
            'id' => $propertyModel->id,
            'slug' => $propertyModel->slug,
            'title' => $propertyModel->titulo,
            'address' => trim($propertyModel->endereco . ' - ' . $propertyModel->bairro . ', ' . $propertyModel->cidade . '/' . $propertyModel->estado),
            'price' => (float) $propertyModel->valor,
            'type' => $propertyModel->businessType?->name ?? $propertyModel->operacao,
            'propertyType' => $propertyModel->propertyType?->nome_tipo ?? '',
            'code' => $propertyModel->codigo_referencia ?: $propertyModel->codigo_anuncio,
            'bedrooms' => (int) ($propertyModel->quartos ?? 0),
            'bathrooms' => (int) ($propertyModel->banheiros ?? 0),
            'garages' => (int) ($propertyModel->garagens ?? 0),
            'area' => (float) ($propertyModel->area_util ?? 0),
            'lotArea' => (float) ($propertyModel->area_total ?? 0),
            'condominium' => (float) ($propertyModel->condominio ?? 0),
            'iptu' => (float) ($propertyModel->iptu ?? 0),
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

    public function storageMedia(string $path): BinaryFileResponse
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        try {
            $mime = File::mimeType($fullPath);
        } catch (\Throwable) {
            $mime = null;
        }

        if (empty($mime)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
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

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Access-Control-Allow-Origin' => '*',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
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

    private function mapOperacaoForXml(Property $property): string
    {
        $name = $property->businessType?->name;
        if ($name === 'Comprar') {
            return 'Venda';
        }
        if ($name === 'Alugar') {
            return 'Aluguel';
        }
        if ($name === 'Temporada') {
            return 'Temporada';
        }
        if (!empty($property->operacao)) {
            return (string) $property->operacao;
        }
        return 'Venda';
    }
}
