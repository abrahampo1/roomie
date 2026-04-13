@props(['title' => 'Dashboard', 'active' => 'index'])

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — Roomie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|fredoka:500,600,700|jetbrains-mono:400,500" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-sand-light text-navy antialiased selection:bg-navy selection:text-cream">

    <svg width="0" height="0" class="absolute">
        <defs>
            <symbol id="roomie-sparkle" viewBox="0 0 24 24">
                <path d="M12 0 C12 5.5 6.5 12 0 12 C6.5 12 12 18.5 12 24 C12 18.5 17.5 12 24 12 C17.5 12 12 5.5 12 0 Z" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-navy text-cream/70 shrink-0 fixed inset-y-0 left-0 z-40" x-data="{ collapsed: false }">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5 px-5 py-5 border-b border-cream/10">
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2.5 group">
                    <svg class="w-5 h-5 text-copper transition-transform group-hover:rotate-90 duration-500" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="font-[Fredoka] font-semibold text-lg text-cream tracking-tight">Roomie</span>
                </a>
            </div>

            {{-- Nav sections --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <p class="px-3 mb-2 text-[10px] font-mono uppercase tracking-[0.18em] text-cream/30">Panel</p>

                <a href="{{ route('dashboard.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'index' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                    </svg>
                    Resumen
                </a>

                <a href="{{ route('dashboard.analytics') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'analytics' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    Analíticas
                </a>

                <a href="{{ route('dashboard.send-history') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'send-history' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    Historial de envío
                </a>

                <a href="{{ route('dashboard.email-previews') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'email-previews' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    Emails
                </a>

                <div class="pt-4 mt-4 border-t border-cream/10">
                    <p class="px-3 mb-2 text-[10px] font-mono uppercase tracking-[0.18em] text-cream/30">Campañas</p>

                    <a href="{{ route('campaigns.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'campaigns' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        Mis campañas
                    </a>

                    <a href="{{ route('campaigns.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition hover:bg-cream/5 hover:text-cream">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva campaña
                    </a>

                    <a href="{{ route('dashboard.agents') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'agents' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                        Agentes IA
                    </a>

                    <a href="{{ route('dashboard.sequences') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'sequences' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                        </svg>
                        Secuencias
                    </a>
                </div>

                <div class="pt-4 mt-4 border-t border-cream/10">
                    <p class="px-3 mb-2 text-[10px] font-mono uppercase tracking-[0.18em] text-cream/30">Ajustes</p>

                    <a href="{{ route('settings.brand.show') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'brand' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                        </svg>
                        Marca
                    </a>

                    <a href="{{ route('settings.image-bank.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'image-bank' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        Imágenes
                    </a>

                    <a href="{{ route('settings.api-token.show') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ $active === 'api' ? 'bg-cream/10 text-cream font-medium' : 'hover:bg-cream/5 hover:text-cream' }}">
                        <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                        </svg>
                        API y Webhooks
                    </a>
                </div>
            </nav>

            {{-- User footer --}}
            <div class="border-t border-cream/10 px-3 py-3">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-8 h-8 rounded-full bg-copper/20 flex items-center justify-center text-copper text-xs font-[Fredoka] font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-cream truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-cream/40 font-mono truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-cream/40 hover:text-cream transition cursor-pointer" title="Cerrar sesión">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile top bar --}}
        <div class="lg:hidden fixed top-0 inset-x-0 z-40 bg-navy border-b border-cream/10" x-data="{ mobileOpen: false }">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="font-[Fredoka] font-semibold text-cream">Roomie</span>
                </a>
                <button @click="mobileOpen = !mobileOpen" class="text-cream/60 hover:text-cream transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav x-show="mobileOpen" x-cloak @click.away="mobileOpen = false"
                 class="px-4 pb-4 space-y-1 border-t border-cream/10">
                <a href="{{ route('dashboard.index') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Resumen</a>
                <a href="{{ route('dashboard.analytics') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Analíticas</a>
                <a href="{{ route('dashboard.send-history') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Historial</a>
                <a href="{{ route('dashboard.email-previews') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Emails</a>
                <a href="{{ route('campaigns.index') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Campañas</a>
                <a href="{{ route('dashboard.agents') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Agentes IA</a>
                <a href="{{ route('dashboard.sequences') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Secuencias</a>
                <div class="border-t border-cream/10 pt-2 mt-2">
                    <a href="{{ route('settings.brand.show') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Marca</a>
                    <a href="{{ route('settings.image-bank.index') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">Imágenes</a>
                    <a href="{{ route('settings.api-token.show') }}" class="block px-3 py-2 text-sm text-cream/70 hover:text-cream rounded-lg">API</a>
                </div>
            </nav>
        </div>

        {{-- Main content --}}
        <main class="flex-1 lg:ml-64 min-h-screen">
            <div class="pt-14 lg:pt-0">
                <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                    @if (session('message'))
                        <div class="mb-6 border-l-2 border-copper pl-4 py-1 text-sm text-navy/70">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
