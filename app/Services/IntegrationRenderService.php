<?php

namespace App\Services;

use App\Models\CustomScript;
use App\Models\Integration;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class IntegrationRenderService
{
    public function __construct(
        private readonly IntegrationCacheService $cache,
    ) {
    }

    public function renderHead(?Request $request = null): HtmlString
    {
        $request = $request ?: request();

        if ($this->isAdminRoute($request)) {
            return new HtmlString('');
        }

        $html = array_merge(
            $this->renderHeadIntegrations(),
            $this->renderCustomScripts($request, CustomScript::LOCATION_HEAD),
        );

        return new HtmlString(implode("\n", array_filter($html)));
    }

    public function renderHeadEarly(?Request $request = null): HtmlString
    {
        $request = $request ?: request();

        if ($this->isAdminRoute($request)) {
            return new HtmlString('');
        }

        return new HtmlString(implode("\n", array_filter($this->renderHeadIntegrations())));
    }

    public function renderHeadLate(?Request $request = null): HtmlString
    {
        $request = $request ?: request();

        if ($this->isAdminRoute($request)) {
            return new HtmlString('');
        }

        return new HtmlString(implode("\n", $this->renderCustomScripts($request, CustomScript::LOCATION_HEAD)));
    }

    public function renderBodyStart(?Request $request = null): HtmlString
    {
        $request = $request ?: request();

        if ($this->isAdminRoute($request)) {
            return new HtmlString('');
        }

        $html = [];
        $integrations = $this->cache->activeIntegrations();

        $gtm = $integrations->get(Integration::KEY_GTM);
        if ($gtm?->is_active && filled($gtm->external_id)) {
            $html[] = $this->renderGoogleTagManagerNoscript($gtm->external_id);
        }

        $html = array_merge($html, $this->renderCustomScripts($request, CustomScript::LOCATION_BODY_START));

        return new HtmlString(implode("\n", array_filter($html)));
    }

    public function renderBodyEnd(?Request $request = null): HtmlString
    {
        $request = $request ?: request();

        if ($this->isAdminRoute($request)) {
            return new HtmlString('');
        }

        $html = $this->renderCustomScripts($request, CustomScript::LOCATION_BODY_END);

        return new HtmlString(implode("\n", array_filter($html)));
    }

    public function customScriptPageOptions(): array
    {
        $options = collect(config('integrations.public_pages', []))
            ->map(fn (array $item) => [
                'value' => (string) ($item['value'] ?? ''),
                'label' => (string) ($item['label'] ?? ''),
                'group' => 'Rotas públicas',
            ])
            ->filter(fn (array $item) => filled($item['value']) && filled($item['label']))
            ->values();

        $cmsPages = Page::query()
            ->where('ativo', true)
            ->orderBy('titulo')
            ->get(['slug', 'titulo'])
            ->map(fn (Page $page) => [
                'value' => 'page:' . $page->slug,
                'label' => $page->titulo ?: $page->slug,
                'group' => 'Páginas CMS',
            ]);

        return $options->concat($cmsPages)->values()->all();
    }

    public function customScriptLocations(): array
    {
        return config('integrations.custom_script_locations', []);
    }

    public function customScriptScopes(): array
    {
        return config('integrations.custom_script_scopes', []);
    }

    public function integrationDefinitions(): array
    {
        return [
            Integration::KEY_GTM => [
                'key' => Integration::KEY_GTM,
                'name' => 'Google Tag Manager',
            ],
            Integration::KEY_META_PIXEL => [
                'key' => Integration::KEY_META_PIXEL,
                'name' => 'Meta Pixel',
            ],
        ];
    }

    private function renderCustomScripts(Request $request, string $location): array
    {
        $routeContext = $this->currentPageContext($request);

        return $this->cache->activeCustomScripts()
            ->filter(fn (CustomScript $script) => $script->location === $location && $this->scriptMatchesContext($script, $routeContext))
            ->map(fn (CustomScript $script) => $script->code)
            ->values()
            ->all();
    }

    private function renderHeadIntegrations(): array
    {
        $html = [];
        $integrations = $this->cache->activeIntegrations();

        $gtm = $integrations->get(Integration::KEY_GTM);
        if ($gtm?->is_active && filled($gtm->external_id)) {
            $html[] = $this->renderGoogleTagManagerHead($gtm->external_id);
        }

        $meta = $integrations->get(Integration::KEY_META_PIXEL);
        if ($meta?->is_active && filled($meta->external_id)) {
            $html[] = $this->renderMetaPixelHead($meta->external_id);
        }

        return $html;
    }

    private function scriptMatchesContext(CustomScript $script, array $context): bool
    {
        if (!$script->is_active) {
            return false;
        }

        if ($script->scope === CustomScript::SCOPE_SITEWIDE) {
            return true;
        }

        if ($script->scope !== CustomScript::SCOPE_PAGE) {
            return false;
        }

        $targetType = $script->page_target_type;
        $targetValue = $script->page_target_value;

        if (!filled($targetType) || !filled($targetValue)) {
            return false;
        }

        if ($targetType === 'route') {
            return ($context['route_name'] ?? null) === $targetValue;
        }

        if ($targetType === 'page') {
            return ($context['page_slug'] ?? null) === $targetValue;
        }

        return false;
    }

    private function currentPageContext(Request $request): array
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $page = $route?->parameter('page');
        $pageSlug = null;

        if ($page instanceof Page) {
            $pageSlug = $page->slug;
        } elseif (is_string($page)) {
            $pageSlug = $page;
        }

        return [
            'route_name' => $routeName,
            'page_slug' => $pageSlug,
        ];
    }

    private function isAdminRoute(Request $request): bool
    {
        return $request->routeIs('admin.*');
    }

    private function renderGoogleTagManagerHead(string $containerId): string
    {
        $containerId = e($containerId);

        return <<<HTML
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$containerId}');</script>
<!-- End Google Tag Manager -->
HTML;
    }

    private function renderGoogleTagManagerNoscript(string $containerId): string
    {
        $containerId = e($containerId);

        return <<<HTML
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$containerId}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;
    }

    private function renderMetaPixelHead(string $pixelId): string
    {
        $pixelId = e($pixelId);

        return <<<HTML
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
HTML;
    }

}
