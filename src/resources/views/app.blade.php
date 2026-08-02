<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark'=> ($appearance ??
'system') == 'dark']) @if(isset($colorScheme) && $colorScheme !== 'default') data-theme="{{ $colorScheme }}" @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }

                const colorScheme = localStorage.getItem('color-scheme');
                if (colorScheme && colorScheme !== 'default') {
                    document.documentElement.setAttribute('data-theme', colorScheme);
                }
            })();
    </script>

    <style>
        html {
            background-color: var(--background);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @if($page['component'] === 'share/show')
        @php($shareFeed = $page['props']['feed'])
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="PodKeep">
        <meta property="og:title" content="{{ $shareFeed['title'] }}">
        <meta property="og:description" content="{{ $shareFeed['description'] ?? 'Listen on PodKeep.' }}">
        <meta property="og:url" content="{{ url()->full() }}">
        <meta name="twitter:card" content="{{ $shareFeed['cover_image_url'] ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $shareFeed['title'] }}">
        <meta name="twitter:description" content="{{ $shareFeed['description'] ?? 'Listen on PodKeep.' }}">
        @if($shareFeed['cover_image_url'])
            <meta property="og:image" content="{{ $shareFeed['cover_image_url'] }}">
            <meta name="twitter:image" content="{{ $shareFeed['cover_image_url'] }}">
        @endif
    @endif

    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
