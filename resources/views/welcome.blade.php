<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="{{ __('pwa.meta_description') }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MansaVibes">

    <title>MANSA VIBES — {{ __('Plateforme SaaS ateliers de couture') }}</title>

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('icons/mansavibes-icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('icons/mansavibes-icon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mansa-black text-white antialiased font-sans">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-24 w-96 h-96 rounded-full bg-gold-500/25 blur-3xl"></div>
        <div class="absolute top-1/2 -left-32 w-80 h-80 rounded-full bg-gold-600/15 blur-3xl"></div>
    </div>

    <header class="relative border-b border-gold-600/25">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-white">
                MANSA <span class="text-gold-400">VIBES</span>
            </a>
            <nav class="flex items-center gap-3 text-sm">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="text-white/70 hover:text-gold-300 transition">Connexion</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gold-500 text-mansa-black font-semibold hover:bg-gold-400 transition">
                        Créer mon atelier
                    </a>
                @endif
            </nav>
        </div>
    </header>

    <main class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="max-w-2xl">
            <p class="text-gold-400 text-sm font-medium uppercase tracking-wider mb-4">SaaS · Couture &amp; mode · Afrique</p>
            <h1 class="text-4xl sm:text-5xl font-bold text-white leading-tight">
                Digitalisez votre atelier, centralisez votre activité.
            </h1>
            <p class="mt-6 text-lg text-white/70 leading-relaxed">
                <strong class="text-white">Mansa Vibes</strong> regroupe clients, commandes, finances, stocks,
                production et communication dans une seule application — pour des ateliers plus organisés et rentables.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-white text-mansa-black font-semibold hover:bg-gold-100 transition">
                        Commencer gratuitement
                    </a>
                @endif
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-gold-500/40 text-white font-medium hover:bg-white/5 hover:border-gold-400/60 transition">
                        J’ai déjà un compte
                    </a>
                @endif
            </div>
        </div>

        <section class="mt-20 sm:mt-28 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $modules = [
                    ['Commandes', 'Suivi des statuts, affectation, historique'],
                    ['CRM clients', 'Fiches, mensurations, dettes'],
                    ['Finance', 'Entrées / sorties, caisse, bilan'],
                    ['Facturation', 'Devis, factures PDF, paiements'],
                    ['Stock & tissus', 'Articles mercerie, alertes, mouvements'],
                    ['Équipe', 'Tailleurs, tâches, performance'],
                    ['E-commerce', 'Vitrine, catalogue, panier'],
                    ['Reporting', 'CA, dépenses, tableaux de bord'],
                    ['Communication', 'Alertes & notifications'],
                ];
            @endphp
            @foreach ($modules as [$title, $desc])
                <div class="rounded-2xl bg-white/5 border border-gold-600/20 p-5 hover:border-gold-400/40 transition">
                    <h2 class="font-semibold text-white">{{ $title }}</h2>
                    <p class="mt-2 text-sm text-white/60">{{ $desc }}</p>
                </div>
            @endforeach
        </section>

        <p class="mt-16 text-center text-white/45 text-sm">
            PWA — installez l’app depuis votre navigateur une fois connecté au tableau de bord.
        </p>
    </main>
</body>
</html>
