<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Roomie' }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|fredoka:600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream text-navy antialiased">
    <nav class="border-b border-sand bg-white/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}" alt="Roomie" class="w-8 h-8">
                <span class="text-xl font-bold tracking-tight font-[Fredoka]">Roomie</span>
            </a>
            <div class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('campaigns.index') }}" class="text-navy/60 hover:text-navy transition">Campañas</a>
                <a href="{{ route('campaigns.create') }}" class="bg-navy text-sand-light px-4 py-2 rounded-lg hover:bg-navy-light transition">
                    Nueva campaña
                </a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-6 py-10">
        @if (session('message'))
            <div class="mb-6 rounded-lg bg-navy/5 border border-navy/10 px-4 py-3 text-sm text-navy">
                {{ session('message') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-sand mt-20 py-8 text-center text-sm text-navy/40">
        Roomie &mdash; Impacthon 2026 &middot; Reto Eurostars Hotel Company
    </footer>

    @stack('scripts')
</body>
</html>
