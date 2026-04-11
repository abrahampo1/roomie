<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Roomie' }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|fredoka:500,600,700|jetbrains-mono:400,500" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream text-navy antialiased selection:bg-navy selection:text-cream">

    <svg width="0" height="0" class="absolute">
        <defs>
            <symbol id="roomie-sparkle" viewBox="0 0 24 24">
                <path d="M12 0 C12 5.5 6.5 12 0 12 C6.5 12 12 18.5 12 24 C12 18.5 17.5 12 24 12 C17.5 12 12 5.5 12 0 Z" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    <nav class="border-b border-navy/15">
        <div class="mx-auto max-w-5xl px-6 py-5 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <svg class="w-[18px] h-[18px] text-copper transition-transform group-hover:rotate-90 duration-500" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                <span class="font-[Fredoka] font-semibold text-xl tracking-tight">Roomie</span>
            </a>
            <div class="flex items-center gap-7 text-sm">
                <a href="{{ route('campaigns.index') }}" class="text-navy/55 hover:text-navy transition">
                    Campañas
                </a>
                <a href="{{ route('campaigns.create') }}" class="bg-navy text-cream px-4 py-2 rounded-full hover:bg-navy-light transition">
                    Nueva
                </a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-6 py-14">
        @if (session('message'))
            <div class="mb-10 border-l-2 border-copper pl-4 py-1 text-sm text-navy/70">
                {{ session('message') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-navy/15 mt-28">
        <div class="mx-auto max-w-5xl px-6 py-10 flex flex-wrap items-baseline justify-between gap-4">
            <div>
                <p class="font-[Fredoka] font-semibold text-base">Roomie</p>
                <p class="text-xs text-navy/45 mt-1">Reto Eurostars Hotel Company · Impacthon 2026</p>
            </div>
            <p class="text-xs text-navy/40 italic font-[Fredoka]">"Make me want to travel"</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
