<x-layouts.app title="Home">
    {{-- ═══════════════════════ HERO ═══════════════════════ --}}
    <section class="relative -mt-10 pt-16 pb-24 overflow-hidden">
        {{-- Floating brand decorations --}}
        <svg class="absolute top-10 right-[8%] w-24 h-24 text-copper opacity-80 animate-[spin_22s_linear_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>
        <svg class="absolute top-[55%] left-[3%] w-12 h-12 text-navy animate-[float_7s_ease-in-out_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>
        <svg class="absolute bottom-20 right-[15%] w-8 h-8 text-navy/60 animate-[float_10s_ease-in-out_1.5s_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>
        <svg class="absolute top-[12%] left-[45%] w-5 h-5 text-copper animate-[blink_1.8s_ease-in-out_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>

        <div class="relative">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-navy text-cream px-4 py-2 rounded-full mb-8 border-2 border-navy shadow-[4px_4px_0_0_#c8956c]">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-copper opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-copper"></span>
                </span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.2em]">04 agentes · 01 pipeline</span>
            </div>

            {{-- Huge display typography --}}
            <h1 class="font-[Fredoka] font-bold leading-[0.85] tracking-tighter text-[clamp(3rem,10vw,9rem)]">
                Make me<br>
                <span class="text-outline-thick">want&nbsp;to</span><br>
                <span class="inline-flex items-baseline gap-3">
                    <span class="text-copper">travel</span>
                    <svg class="w-[0.75em] h-[0.75em] text-navy animate-[spin_14s_linear_infinite]" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </span>
            </h1>

            <p class="max-w-xl text-lg text-navy/65 mt-10 leading-relaxed">
                Campañas de marketing hotelero hiper-personalizadas,
                diseñadas por 4 agentes de IA que analizan, estrategizan, crean y auditan.
            </p>

            <div class="flex flex-wrap gap-4 mt-10">
                <a href="{{ route('campaigns.create') }}"
                   class="group inline-flex items-center gap-3 bg-navy text-cream px-7 py-4 rounded-2xl font-semibold border-2 border-navy shadow-[6px_6px_0_0_#c8956c] hover:shadow-[3px_3px_0_0_#c8956c] hover:translate-x-[3px] hover:translate-y-[3px] transition-all">
                    Crear campaña
                    <svg class="w-4 h-4 text-copper group-hover:rotate-90 transition-transform" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </a>
                <a href="{{ route('campaigns.index') }}"
                   class="inline-flex items-center gap-3 bg-cream px-7 py-4 rounded-2xl font-semibold border-2 border-navy hover:bg-sand-light transition-colors">
                    Ver historial
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ MARQUEE ═══════════════════════ --}}
    <div class="relative -mx-6 bg-navy text-cream border-y-2 border-navy overflow-hidden">
        <div class="absolute inset-0 opacity-[0.06] text-cream">
            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-diag)"/></svg>
        </div>
        <div class="relative flex items-center whitespace-nowrap py-5 animate-[marquee_40s_linear_infinite] text-3xl font-[Fredoka] font-semibold">
            @for ($i = 0; $i < 2; $i++)
                <span class="flex items-center shrink-0">
                    <span class="mx-8">Analista</span>
                    <svg class="w-6 h-6 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="mx-8 text-outline-cream">Estratega</span>
                    <svg class="w-6 h-6 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="mx-8">Creativo</span>
                    <svg class="w-6 h-6 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="mx-8 text-outline-cream">Auditor</span>
                    <svg class="w-6 h-6 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <span class="mx-8 text-copper">Hyper-personalized</span>
                    <svg class="w-6 h-6 text-copper shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </span>
            @endfor
        </div>
    </div>

    {{-- ═══════════════════════ PIPELINE BENTO ═══════════════════════ --}}
    <section class="mt-24">
        <div class="flex items-end justify-between mb-10 flex-wrap gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-copper font-bold mb-2">/ the pipeline</p>
                <h2 class="font-[Fredoka] font-bold text-5xl tracking-tight leading-none">
                    Cuatro cerebros.<br>
                    <span class="text-outline-thick">Un resultado.</span>
                </h2>
            </div>
            <p class="text-sm text-navy/50 max-w-xs text-right">
                Cada agente tiene una especialidad y pasa el contexto al siguiente. Así se construye una campaña.
            </p>
        </div>

        <div class="grid grid-cols-12 gap-4">
            {{-- 01 — Analista (grande) --}}
            <div class="col-span-12 md:col-span-7 relative bg-white rounded-3xl border-2 border-navy p-8 overflow-hidden shadow-[6px_6px_0_0_#1a1a2e] min-h-[260px]">
                <div class="absolute inset-0 opacity-[0.05] text-navy">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-dots)"/></svg>
                </div>
                <span class="absolute -top-6 -right-4 text-[12rem] font-[Fredoka] font-bold text-sand leading-none select-none">01</span>
                <div class="relative">
                    <div class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] bg-navy text-copper px-2.5 py-1 rounded-full mb-4 font-bold">
                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        Agent 01
                    </div>
                    <h3 class="text-4xl font-[Fredoka] font-bold mb-3">Analista</h3>
                    <p class="text-navy/60 max-w-sm leading-relaxed">
                        Segmenta la base de clientes, extrae insights de los datos reales y recomienda el foco para la próxima campaña.
                    </p>
                </div>
            </div>

            {{-- 02 — Estratega --}}
            <div class="col-span-12 md:col-span-5 relative bg-copper text-cream rounded-3xl border-2 border-navy p-8 overflow-hidden shadow-[6px_6px_0_0_#1a1a2e] min-h-[260px]">
                <div class="absolute inset-0 opacity-[0.12] text-cream">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-plus)"/></svg>
                </div>
                <span class="absolute -bottom-8 -right-2 text-[10rem] font-[Fredoka] font-bold text-cream/15 leading-none select-none">02</span>
                <div class="relative">
                    <div class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] bg-navy text-copper px-2.5 py-1 rounded-full mb-4 font-bold">
                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        Agent 02
                    </div>
                    <h3 class="text-4xl font-[Fredoka] font-bold mb-3">Estratega</h3>
                    <p class="text-cream/80 leading-relaxed">
                        Define público, hotel, timing y canal óptimos con un mensaje clave.
                    </p>
                </div>
            </div>

            {{-- 03 — Creativo --}}
            <div class="col-span-12 md:col-span-5 relative bg-navy text-cream rounded-3xl border-2 border-navy p-8 overflow-hidden shadow-[6px_6px_0_0_#c8956c] min-h-[260px]">
                <div class="absolute inset-0 opacity-[0.08] text-cream">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
                </div>
                <span class="absolute -top-6 -right-4 text-[12rem] font-[Fredoka] font-bold text-cream/5 leading-none select-none">03</span>
                <div class="relative">
                    <div class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] bg-copper text-navy px-2.5 py-1 rounded-full mb-4 font-bold">
                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        Agent 03
                    </div>
                    <h3 class="text-4xl font-[Fredoka] font-bold mb-3 text-copper">Creativo</h3>
                    <p class="text-cream/75 leading-relaxed max-w-sm">
                        Genera copy, asunto, CTA y formatos alternativos con el tono exacto del segmento.
                    </p>
                </div>
            </div>

            {{-- 04 — Auditor (grande) --}}
            <div class="col-span-12 md:col-span-7 relative bg-sand-light rounded-3xl border-2 border-navy p-8 overflow-hidden shadow-[6px_6px_0_0_#1a1a2e] min-h-[260px]">
                <div class="absolute inset-0 opacity-[0.04] text-navy">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-grid)"/></svg>
                </div>
                <span class="absolute -bottom-10 -right-6 text-[14rem] font-[Fredoka] font-bold text-sand leading-none select-none">04</span>
                <div class="relative">
                    <div class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] bg-navy text-copper px-2.5 py-1 rounded-full mb-4 font-bold">
                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        Agent 04
                    </div>
                    <h3 class="text-4xl font-[Fredoka] font-bold mb-3">Auditor</h3>
                    <p class="text-navy/60 max-w-sm leading-relaxed">
                        Revisa coherencia, detecta incoherencias entre datos, estrategia y creatividad — y entrega un score final de calidad.
                    </p>
                    <div class="flex items-center gap-2 mt-5">
                        <div class="flex-1 h-1.5 bg-navy/10 rounded-full overflow-hidden">
                            <div class="h-full bg-navy w-[87%] rounded-full"></div>
                        </div>
                        <span class="text-xs font-bold">87/100</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ CTA DIAGONAL ═══════════════════════ --}}
    <section class="mt-24 relative overflow-hidden rounded-3xl border-2 border-navy bg-navy text-cream">
        <div class="absolute inset-0 opacity-[0.08] text-cream">
            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
        </div>
        <div class="absolute -top-20 -right-20 w-72 h-72 text-copper/30 animate-[spin_30s_linear_infinite]">
            <svg viewBox="0 0 24 24" class="w-full h-full"><use href="#roomie-sparkle"/></svg>
        </div>
        <div class="relative p-10 md:p-14 flex flex-wrap items-center justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-copper mb-3 font-bold">/ ready?</p>
                <h3 class="font-[Fredoka] font-bold text-4xl md:text-5xl leading-[0.9] tracking-tight">
                    Un objetivo.<br>
                    <span class="text-copper">Una campaña completa.</span>
                </h3>
            </div>
            <a href="{{ route('campaigns.create') }}"
               class="inline-flex items-center gap-3 bg-copper text-navy px-7 py-4 rounded-2xl font-bold border-2 border-cream hover:bg-cream transition-colors">
                Lanzar pipeline
                <svg class="w-4 h-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </a>
        </div>
    </section>
</x-layouts.app>
