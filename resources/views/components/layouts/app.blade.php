<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#faf8f5">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Roomie' }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|fredoka:500,600,700|jetbrains-mono:400,500" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Microsoft Clarity --}}
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "wa03z3i5ic");
    </script>
</head>
<body class="h-full bg-cream text-navy antialiased selection:bg-navy selection:text-cream">

    <svg width="0" height="0" class="absolute">
        <defs>
            <symbol id="roomie-sparkle" viewBox="0 0 24 24">
                <path d="M12 0 C12 5.5 6.5 12 0 12 C6.5 12 12 18.5 12 24 C12 18.5 17.5 12 24 12 C17.5 12 12 5.5 12 0 Z" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    <nav class="sticky top-0 z-40 border-b border-navy/15 bg-cream/85 backdrop-blur-md">
        <div class="mx-auto max-w-5xl px-5 sm:px-6 py-3 sm:py-5 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group -my-2 py-2">
                <svg class="w-[18px] h-[18px] text-copper transition-transform group-hover:rotate-90 duration-500" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                <span class="font-[Fredoka] font-semibold text-xl tracking-tight">Roomie</span>
            </a>
            <div class="flex items-center gap-3 sm:gap-5 text-sm">
                @auth
                    <a href="{{ route('dashboard.index') }}" class="hidden sm:inline text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                        Panel
                    </a>
                    <a href="{{ route('campaigns.index') }}" class="hidden sm:inline text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                        Campañas
                    </a>
                    <a href="{{ route('settings.api-token.show') }}" class="hidden sm:inline text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                        Ajustes
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:flex">
                        @csrf
                        <button type="submit" class="text-navy/55 hover:text-navy transition -my-2 py-2 px-1 cursor-pointer">
                            Salir
                        </button>
                    </form>
                    {{-- Mobile hamburger --}}
                    <div class="relative sm:hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 top-full mt-2 w-48 rounded-xl border border-navy/15 bg-cream shadow-lg py-2 z-50">
                            <a href="{{ route('dashboard.index') }}" class="block px-4 py-2 text-sm text-navy/70 hover:bg-navy/[0.04] transition">Panel</a>
                            <a href="{{ route('campaigns.index') }}" class="block px-4 py-2 text-sm text-navy/70 hover:bg-navy/[0.04] transition">Campañas</a>
                            <a href="{{ route('settings.brand.show') }}" class="block px-4 py-2 text-sm text-navy/70 hover:bg-navy/[0.04] transition">Marca</a>
                            <a href="{{ route('settings.image-bank.index') }}" class="block px-4 py-2 text-sm text-navy/70 hover:bg-navy/[0.04] transition">Imágenes</a>
                            <a href="{{ route('settings.api-token.show') }}" class="block px-4 py-2 text-sm text-navy/70 hover:bg-navy/[0.04] transition">API</a>
                            <div class="border-t border-navy/10 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-navy/55 hover:bg-navy/[0.04] transition cursor-pointer">Salir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('campaigns.create') }}" class="bg-navy text-cream px-4 py-2.5 rounded-full hover:bg-navy-light transition">
                        Nueva
                    </a>
                @else
                    <a href="{{ route('docs') }}" class="hidden sm:inline text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                        Docs
                    </a>
                    <a href="{{ route('login') }}" class="text-navy/55 hover:text-navy transition -my-2 py-2 px-1">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="bg-navy text-cream px-4 py-2.5 rounded-full hover:bg-navy-light transition">
                        Crear cuenta
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-5 sm:px-6 py-10 sm:py-14">
        @if (session('message'))
            <div class="mb-8 sm:mb-10 border-l-2 border-copper pl-4 py-1 text-sm text-navy/70">
                {{ session('message') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-navy/15 mt-20 sm:mt-28" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto max-w-5xl px-5 sm:px-6 py-9 sm:py-10 flex flex-col sm:flex-row sm:flex-wrap sm:items-baseline sm:justify-between gap-3 sm:gap-4">
            <div>
                <p class="font-[Fredoka] font-semibold text-base">Roomie</p>
                <p class="text-xs text-navy/45 mt-1">Reto Eurostars Hotel Company</p>
            </div>
            <div class="flex items-baseline gap-5">
                <a href="{{ route('docs') }}" class="text-xs text-navy/55 hover:text-navy transition">API docs</a>
                <p class="text-xs text-navy/40 italic font-[Fredoka]">"Make me want to travel"</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
