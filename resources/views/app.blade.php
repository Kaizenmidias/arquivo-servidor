@php
    $integrationRenderer = app(\App\Services\IntegrationRenderService::class);
    $isAdminSurface = request()->routeIs('admin.*') || request()->routeIs('login') || request()->routeIs('login.store') || request()->routeIs('logout');
    $settings = \App\Models\Setting::query()->pluck('valor', 'chave');
    $primary = '#000000';
    $secondary = '#173b2f';
    $button = '#173b2f';
    $footerBg = '#000000';
    $fontFamily = "'Cormorant Garamond', Georgia, serif";
    $fontSizeText = is_numeric($settings['font_size_text'] ?? null) ? (int) $settings['font_size_text'] : 16;
    $fontSizeTitle = is_numeric($settings['font_size_title'] ?? null) ? (int) $settings['font_size_title'] : 40;
    $homeOverlayColor = '#000000';
    $homeOverlayOpacity = is_numeric($settings['home_hero_overlay_opacity'] ?? null) ? (int) $settings['home_hero_overlay_opacity'] : 70;
    $faviconUrl = $settings['favicon_url'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Meteorikah - Negócios imobiliários') }}</title>
    @if (!empty($faviconUrl))
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <style>
        :root {
            --site-primary: {{ $primary }};
            --site-secondary: {{ $secondary }};
            --site-button: {{ $button }};
            --site-footer-bg: {{ $footerBg }};
            --site-font-family: {{ $fontFamily }};
            --site-font-size-text: {{ $fontSizeText }}px;
            --site-font-size-title: {{ $fontSizeTitle }}px;
            --site-home-overlay-color: {{ $homeOverlayColor }};
            --site-home-overlay-opacity: {{ max(0, min(100, $homeOverlayOpacity)) / 100 }};
        }
    </style>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! $integrationRenderer->renderHead() !!}
</head>
<body class="antialiased {{ $isAdminSurface ? 'is-admin' : 'is-site' }}" style="font-family: var(--site-font-family);">
    {!! $integrationRenderer->renderBodyStart() !!}
    @inertia
    {!! $integrationRenderer->renderBodyEnd() !!}
</body>
</html>
