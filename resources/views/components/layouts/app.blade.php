<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Roomie' }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|fredoka:500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-cream text-navy antialiased selection:bg-navy selection:text-cream">

    {{-- Global SVG pattern defs (reusable) --}}
    <svg width="0" height="0" class="absolute">
        <defs>
            <symbol id="roomie-sparkle" viewBox="0 0 24 24">
                <path d="M12 0 C12 5.5 6.5 12 0 12 C6.5 12 12 18.5 12 24 C12 18.5 17.5 12 24 12 C17.5 12 12 5.5 12 0 Z" fill="currentColor"/>
            </symbol>
            <pattern id="pat-stars" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                <use href="#roomie-sparkle" x="15" y="15" width="20" height="20" />
            </pattern>
            <pattern id="pat-dots" x="0" y="0" width="18" height="18" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1.6" fill="currentColor"/>
            </pattern>
            <pattern id="pat-plus" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                <path d="M14 8v12 M8 14h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
            </pattern>
            <pattern id="pat-diag" x="0" y="0" width="12" height="12" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                <line x1="0" y1="0" x2="0" y2="12" stroke="currentColor" stroke-width="1.5"/>
            </pattern>
            <pattern id="pat-grid" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M40 0H0V40" stroke="currentColor" stroke-width="1" fill="none"/>
            </pattern>
        </defs>
    </svg>

    {{-- Nav --}}
    <nav class="sticky top-0 z-50 border-b-2 border-navy bg-cream/90 backdrop-blur-md">
        <div class="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="relative w-9 h-9 flex items-center justify-center rounded-xl bg-navy text-copper transition-transform group-hover:rotate-[18deg]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </div>
                <span class="text-2xl font-bold tracking-tight font-[Fredoka] leading-none">Roomie<span class="text-copper">.</span></span>
            </a>
            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('campaigns.index') }}" class="hidden sm:inline-flex items-center gap-2 text-navy/60 hover:text-navy transition">
                    <span class="w-1.5 h-1.5 rounded-full bg-navy/30"></span>
                    Campañas
                </a>
                <a href="{{ route('campaigns.create') }}"
                   class="relative inline-flex items-center gap-2 bg-navy text-cream px-4 py-2 rounded-xl hover:bg-navy-light transition border-2 border-navy shadow-[4px_4px_0_0_#c8956c] hover:shadow-[2px_2px_0_0_#c8956c] hover:translate-x-[2px] hover:translate-y-[2px]">
                    <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    Nueva campaña
                </a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-6 py-10 relative">
        @if (session('message'))
            <div class="mb-6 rounded-xl bg-navy text-cream border-2 border-navy px-4 py-3 text-sm flex items-center gap-3 shadow-[4px_4px_0_0_#c8956c]">
                <svg class="w-4 h-4 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                {{ session('message') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Footer with giant outline text --}}
    <footer class="relative mt-32 border-t-2 border-navy bg-navy text-cream overflow-hidden">
        <div class="absolute inset-0 opacity-[0.08] text-cream">
            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
        </div>
        <div class="relative mx-auto max-w-6xl px-6 pt-16 pb-6">
            <div class="flex flex-wrap items-end justify-between gap-8 mb-12">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-copper mb-3">Impacthon 2026</p>
                    <p class="text-2xl font-[Fredoka] font-bold leading-tight max-w-md">
                        Reto Eurostars Hotel Company.<br>
                        <span class="text-copper">Hacemos que quieras viajar.</span>
                    </p>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('campaigns.index') }}" class="hover:text-copper transition">Campañas</a>
                    <a href="{{ route('campaigns.create') }}" class="hover:text-copper transition">Nueva</a>
                </div>
            </div>

            <div class="relative">
                <h2 class="text-outline-cream font-[Fredoka] font-bold leading-[0.8] tracking-tighter text-[clamp(4rem,22vw,18rem)] select-none text-center">
                    ROOMIE
                </h2>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-cream/20 text-xs text-cream/50">
                <p>&copy; {{ date('Y') }} Roomie · Built with Laravel + 4 AI agents</p>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-acid animate-[blink_1.8s_ease-in-out_infinite]"></span>
                    Pipeline online
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
