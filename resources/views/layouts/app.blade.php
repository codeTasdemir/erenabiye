<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $seoDefaults = \App\Services\SeoService::defaults();
        $finalTitle = $seoTitle ?? $seoDefaults['title'];
        $finalDesc = $seoDescription ?? $seoDefaults['description'];
        $finalKeys = $seoKeywords ?? $seoDefaults['keywords'];
        $finalImage = $seoImage ?? $seoDefaults['image'];
        $finalUrl = $seoUrl ?? $seoDefaults['url'];
        $finalSite = $seoDefaults['site_name'];
    @endphp

    {{-- Primary Meta --}}
    <title>{{ $finalTitle }}</title>
    <meta name="description" content="{{ $finalDesc }}">
    <meta name="keywords" content="{{ $finalKeys }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $finalUrl }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $finalTitle }}">
    <meta property="og:description" content="{{ $finalDesc }}">
    <meta property="og:image" content="{{ $finalImage }}">
    <meta property="og:url" content="{{ $finalUrl }}">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="{{ $finalSite }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $finalTitle }}">
    <meta name="twitter:description" content="{{ $finalDesc }}">
    <meta name="twitter:image" content="{{ $finalImage }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap"
        rel="stylesheet">

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire --}}
    @livewireStyles

    {{-- Alpine.js --}}
{{--     <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
 --}}
    {{-- Google Analytics --}}
    @if ($gaId = \App\Models\Setting::get('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif
</head>

<body>
    <div class="max-w-screen-xl mx-auto px-4">
        @livewire('layout.header')

        <main>
            {{ $slot }}
        </main>
    </div>
    @livewire('layout.footer')

    @livewireScripts
</body>

</html>
