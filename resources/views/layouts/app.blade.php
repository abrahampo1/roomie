<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Roomie' }} — Roomie</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream text-navy antialiased">
    <nav class="border-b border-sand bg-white/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-navy flex items-center justify-center">
                    <svg class="w-5 h-5 text-sand-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                    </svg>
                </div>
                <span class="text-xl font-semibold tracking-tight">Roomie</span>
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
