<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $defaultSiteName = \App\Models\Setting::getValue('site_name', config('app.name', 'Avenir Research'));
            $defaultDescription = \App\Models\Setting::getValue('site_description', 'AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif.');
            
            $seoTitle = $meta['title'] ?? $defaultSiteName;
            $seoDesc = $meta['description'] ?? $defaultDescription;
            $seoImage = $meta['image'] ?? asset('favicon.png');
            $seoType = $meta['type'] ?? 'website';
            $seoTwitter = $meta['twitter_card'] ?? 'summary_large_image';
        @endphp

        <title inertia>{{ $seoTitle }}</title>
        
        <!-- Primary Meta Tags -->
        <meta name="title" content="{{ $seoTitle }}">
        <meta name="description" content="{{ $seoDesc }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="{{ $seoType }}">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDesc }}">
        <meta property="og:image" content="{{ $seoImage }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="{{ $seoTwitter }}">
        <meta property="twitter:url" content="{{ request()->url() }}">
        <meta property="twitter:title" content="{{ $seoTitle }}">
        <meta property="twitter:description" content="{{ $seoDesc }}">
        <meta property="twitter:image" content="{{ $seoImage }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

        <!-- Favicon -->
        <link rel="shortcut icon" href="/favicon.ico?v=2" type="image/x-icon">
        <link rel="icon" href="/favicon.png?v=2" type="image/png" sizes="64x64">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
