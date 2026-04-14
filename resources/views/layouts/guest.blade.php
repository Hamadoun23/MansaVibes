<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#c5a028">
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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-mansa-black antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-mansa-black px-4 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none opacity-40">
                <div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-gold-500/25 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-gold-600/15 blur-3xl"></div>
            </div>
            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-1">
                    <span class="text-xl font-bold tracking-tight text-white">MANSA <span class="text-gold-400">VIBES</span></span>
                    <span class="text-xs text-gold-200/80 uppercase tracking-widest">Atelier connecté</span>
                </a>
            </div>

            <div class="relative w-full sm:max-w-md mt-8 px-6 py-6 bg-white shadow-xl border border-gold-200/80 border-t-4 border-t-gold-500 overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
