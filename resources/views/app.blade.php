<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    @php
        $settings = \App\Models\Setting::query()->pluck('valor', 'chave');
        $primary = $settings['primary_color'] ?? '#1e3a8a';
        $secondary = $settings['secondary_color'] ?? '#f97316';
        $button = $settings['button_color'] ?? $secondary;
        $footerBg = $settings['footer_bg_color'] ?? '#111827';
        $fontFamily = $settings['font_family'] ?? "Instrument Sans, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif";
        $fontSizeText = is_numeric($settings['font_size_text'] ?? null) ? (int) $settings['font_size_text'] : 16;
        $fontSizeTitle = is_numeric($settings['font_size_title'] ?? null) ? (int) $settings['font_size_title'] : 40;
        $homeOverlayColor = $settings['home_hero_overlay_color'] ?? '#0f172a';
        $homeOverlayOpacity = is_numeric($settings['home_hero_overlay_opacity'] ?? null) ? (int) $settings['home_hero_overlay_opacity'] : 70;
        $faviconUrl = $settings['favicon_url'] ?? null;
    @endphp
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
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased {{ request()->is('admin*') ? 'is-admin' : 'is-site' }}" style="font-family: var(--site-font-family);">
    @inertia
</body>
</html>
