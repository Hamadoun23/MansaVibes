<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#c5a028">
        <meta name="application-name" content="MANSA VIBES">
        <meta name="description" content="{{ __('pwa.meta_description') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="MansaVibes">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" href="{{ asset('icons/mansavibes-icon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('icons/mansavibes-icon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased bg-stone-200 text-mansa-black">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b-2 border-gold-500 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-mansa-black">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
