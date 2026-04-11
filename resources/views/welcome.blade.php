<x-layouts.app title="Inicio">
    {{-- ═══════════════════════════════════════ HERO ═══════════════════════════════════════ --}}
    <section class="relative pt-2 sm:pt-6 pb-20 sm:pb-28 overflow-hidden">
        {{-- M shape as background watermark, tinted via CSS mask --}}
        <div
            class="pointer-events-none absolute -right-16 sm:right-2 -bottom-24 sm:-bottom-32 w-[360px] sm:w-[520px] md:w-[620px] h-[200px] sm:h-[290px] md:h-[345px] bg-copper"
            style="mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                   -webkit-mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                   mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat;
                   mask-size: contain; -webkit-mask-size: contain;
                   mask-position: center; -webkit-mask-position: center;
                   opacity: 0.11; transform: rotate(-3deg);"
            aria-hidden="true"
        ></div>

        {{-- Floating sparkle constellation --}}
        <svg class="absolute top-3 right-10 sm:right-24 w-4 h-4 text-copper/70" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
        <svg class="absolute top-20 sm:top-28 right-[38%] w-2 h-2 text-navy/25" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
        <svg class="absolute top-40 sm:top-48 left-[60%] w-3 h-3 text-copper/40 hidden sm:block" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>

        <div class="relative max-w-3xl">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-6 sm:mb-7">
                Roomie · Eurostars Hotel Company · Impacthon 2026
            </p>

            <h1 class="font-[Fredoka] font-semibold text-[2.5rem] sm:text-6xl md:text-[4.5rem] leading-[0.96] tracking-tight mb-7">
                Tú escribes<br>
                el objetivo.<br>
                <span class="inline-flex items-center gap-3 sm:gap-4">
                    <span class="text-copper">Cuatro agentes</span>
                    <svg class="w-7 h-7 sm:w-10 sm:h-10 md:w-12 md:h-12 text-copper shrink-0" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                </span><br>
                hacen el resto.
            </h1>

            <p class="text-base sm:text-lg text-navy/65 leading-relaxed max-w-xl mb-8 sm:mb-10">
                Roomie es un pipeline de IA que convierte un briefing de marketing en una campaña hotelera lista para enviar. Lee los datos reales de reservas, decide a quién apuntar y escribe el email — todo en menos de un minuto.
            </p>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
                @auth
                    <a href="{{ route('campaigns.create') }}"
                       class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition w-full sm:w-auto">
                        Crear una campaña
                        <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                    </a>
                    <a href="{{ route('campaigns.index') }}" class="text-sm text-navy/60 hover:text-navy underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition py-2 -my-2">
                        Ver campañas anteriores
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition w-full sm:w-auto">
                        Crear cuenta y empezar
                        <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="text-sm text-navy/60 hover:text-navy underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition py-2 -my-2">
                        Ya tengo cuenta
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════ DIVIDER — sparkle rhythm ═══════════════════════════════════════ --}}
    <div class="flex items-center justify-center gap-5 sm:gap-7 py-2 sm:py-4" aria-hidden="true">
        <span class="h-px flex-1 max-w-[80px] bg-navy/10"></span>
        <svg class="w-2.5 h-2.5 text-navy/20" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
        <svg class="w-4 h-4 text-copper/60" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
        <svg class="w-2.5 h-2.5 text-navy/20" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
        <span class="h-px flex-1 max-w-[80px] bg-navy/10"></span>
    </div>

    {{-- ═══════════════════════════════════════ EJEMPLO REAL — brief → email ═══════════════════════════════════════ --}}
    <section class="pt-14 sm:pt-20 pb-4">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/40 mb-3">
            Ejemplo real
        </p>
        <h2 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl md:text-[2.75rem] leading-[1.02] tracking-tight mb-12 sm:mb-14 max-w-3xl">
            Una frase entra.<br>
            <span class="text-copper">Una campaña sale.</span>
        </h2>

        <div class="grid md:grid-cols-12 gap-8 md:gap-10 items-center">
            {{-- The brief (left) --}}
            <div class="md:col-span-5 relative">
                <svg class="absolute -top-4 -left-2 w-8 h-8 text-copper/25" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                <p class="pl-8 text-xl sm:text-2xl font-[Fredoka] font-medium leading-[1.2] text-navy/85 italic">
                    Subir la ocupación del Áurea Catedral en Granada durante junio, dirigido a parejas internacionales que buscan escapadas culturales.
                </p>
                <p class="mt-4 pl-8 text-[10px] text-navy/45 font-mono uppercase tracking-[0.18em]">Tu briefing · 18 palabras</p>
            </div>

            {{-- Arrow --}}
            <div class="md:col-span-2 flex justify-center">
                <div class="flex md:flex-col items-center gap-3 text-copper">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                    <svg class="w-6 h-6 hidden md:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m0 0l-5-5m5 5l5-5"/>
                    </svg>
                    <svg class="w-6 h-6 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m0 0l-5-5m5 5l-5 5"/>
                    </svg>
                    <svg class="w-3 h-3" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                </div>
            </div>

            {{-- The email output (right) --}}
            <div class="md:col-span-5 rounded-2xl border border-navy/15 bg-white overflow-hidden shadow-[0_1px_0_0_rgba(26,26,46,0.04),0_12px_40px_-20px_rgba(26,26,46,0.18)]">
                <div class="px-5 py-4 border-b border-navy/10 bg-sand-light/40">
                    <p class="text-[10px] text-navy/40 uppercase tracking-[0.18em]">Asunto</p>
                    <p class="font-[Fredoka] font-semibold text-[15px] leading-tight mt-1">Granada de junio. Contigo y nadie más.</p>
                </div>
                <div class="relative bg-navy text-cream px-5 py-6 overflow-hidden">
                    <div
                        class="pointer-events-none absolute -right-10 -bottom-8 w-56 h-32 bg-copper"
                        style="mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                               -webkit-mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                               mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat;
                               mask-size: contain; -webkit-mask-size: contain;
                               mask-position: center; -webkit-mask-position: center;
                               opacity: 0.18;"
                        aria-hidden="true"
                    ></div>
                    <div class="relative">
                        <p class="text-[9px] tracking-[0.18em] uppercase text-copper mb-2 flex items-center gap-1.5">
                            <svg class="w-2 h-2" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                            Áurea Catedral
                        </p>
                        <h3 class="font-[Fredoka] font-semibold text-lg sm:text-xl leading-[1.15]">Los atardeceres más lentos del verano.</h3>
                    </div>
                </div>
                <div class="px-5 py-4 text-[13px] text-navy/65 leading-relaxed">
                    Una Granada sin multitudes, con cenas en patios escondidos y el Albaicín solo para vosotros. Porque en junio, la ciudad vuelve a ser de quien sabe mirarla despacio…
                </div>
                <div class="px-5 pb-5">
                    <span class="block bg-copper text-navy text-center py-2.5 rounded-full text-[13px] font-medium">Reservar las noches de junio</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════ CÓMO FUNCIONA — big numbers ═══════════════════════════════════════ --}}
    <section class="pt-20 sm:pt-28 pb-8">
        <div class="mb-12 sm:mb-14">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/40 mb-3">
                Cómo funciona
            </p>
            <h2 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl md:text-[2.75rem] leading-[1.02] tracking-tight max-w-2xl">
                Cuatro agentes,<br>
                uno detrás de otro, <span class="text-copper">pasando contexto</span>.
            </h2>
        </div>

        <div>
            @foreach ([
                ['Analista', 'Lee la base de clientes y los datos de reservas. Identifica segmentos, patrones y momentos. Decide a quién merece la pena apuntar.'],
                ['Estratega', 'Toma el segmento elegido y le asigna un hotel, un canal y un momento. Define el mensaje clave y el tono que mejor encaja.'],
                ['Creativo', 'Escribe el asunto, el preview, el cuerpo del email y el CTA. Propone formatos alternativos por si quieres probar A/B.'],
                ['Auditor', 'Cruza la creatividad con la estrategia y los datos del análisis. Si algo no encaja, lo señala. Devuelve un score de calidad.'],
            ] as $i => [$name, $desc])
                <div class="grid grid-cols-12 gap-4 sm:gap-6 py-8 sm:py-10 border-t border-navy/10 last:border-b items-start">
                    <span class="col-span-3 sm:col-span-3 md:col-span-2 font-[Fredoka] font-semibold text-[4.5rem] sm:text-[6rem] md:text-[7rem] leading-[0.78] text-sand select-none tracking-tighter">
                        {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="col-span-9 sm:col-span-9 md:col-span-10 sm:pt-3">
                        <div class="flex items-center gap-2 mb-2 sm:mb-3">
                            <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-copper font-medium">Agente</p>
                        </div>
                        <h3 class="font-[Fredoka] font-semibold text-2xl sm:text-[1.75rem] leading-tight mb-3">{{ $name }}</h3>
                        <p class="text-navy/65 leading-relaxed max-w-2xl text-[15px] sm:text-base">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══════════════════════════════════════ CLOSING CTA — inverted navy with M watermark ═══════════════════════════════════════ --}}
    <section class="relative mt-20 sm:mt-28 py-16 sm:py-24 px-6 sm:px-10 bg-navy text-cream rounded-3xl overflow-hidden">
        {{-- M shape as background watermark --}}
        <div
            class="pointer-events-none absolute -left-16 sm:-left-8 -bottom-10 w-[420px] sm:w-[560px] h-[230px] sm:h-[310px] bg-copper"
            style="mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                   -webkit-mask-image: url('{{ asset('images/brand/LogoRoomie_M.svg') }}');
                   mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat;
                   mask-size: contain; -webkit-mask-size: contain;
                   mask-position: center; -webkit-mask-position: center;
                   opacity: 0.15; transform: rotate(4deg);"
            aria-hidden="true"
        ></div>

        {{-- Scattered sparkles --}}
        <svg class="absolute top-8 right-10 w-4 h-4 text-copper/60" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
        <svg class="absolute top-20 right-1/3 w-2 h-2 text-copper/40" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
        <svg class="absolute bottom-12 right-24 w-3 h-3 text-cream/20" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>

        <div class="relative max-w-3xl">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-copper mb-5">
                Roomie · listo cuando tú quieras
            </p>
            <h2 class="font-[Fredoka] font-semibold text-[2rem] sm:text-4xl md:text-[3rem] leading-[1.02] tracking-tight mb-6">
                ¿Listo para dejar de escribir<br>
                <span class="text-copper italic">"Escápate este fin de semana"</span>?
            </h2>
            <p class="text-cream/70 text-base sm:text-lg leading-relaxed max-w-xl mb-9">
                Una campaña completa — estrategia, email, formatos alternativos y auditoría de calidad — en menos de un minuto.
            </p>
            <a href="{{ auth()->check() ? route('campaigns.create') : route('register') }}"
               class="inline-flex items-center justify-center gap-2 bg-copper text-navy pl-7 pr-6 py-3.5 rounded-full font-medium hover:bg-cream transition w-full sm:w-auto">
                {{ auth()->check() ? 'Crear mi campaña' : 'Empezar gratis' }}
                <svg class="w-3 h-3 text-navy" viewBox="0 0 24 24" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
            </a>
        </div>
    </section>
</x-layouts.app>
