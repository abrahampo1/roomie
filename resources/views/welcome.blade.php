<x-layouts.app title="Inicio">
    {{-- Hero --}}
    <section class="max-w-3xl pt-2 sm:pt-6 pb-16 sm:pb-20">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-6 sm:mb-7">
            Roomie · Eurostars Hotel Company · Impacthon 2026
        </p>

        <h1 class="font-[Fredoka] font-semibold text-[2.5rem] sm:text-5xl md:text-[3.75rem] leading-[1.02] tracking-tight mb-6 sm:mb-7">
            Tú escribes el objetivo.<br>
            <span class="text-copper">Cuatro agentes</span> hacen el resto.
        </h1>

        <p class="text-base sm:text-lg text-navy/65 leading-relaxed max-w-xl mb-8 sm:mb-9">
            Roomie es un pipeline de IA que convierte un briefing de marketing en una campaña hotelera lista para enviar.
            Lee los datos reales de reservas, decide a quién apuntar y escribe el email — todo en menos de un minuto.
        </p>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
            <a href="{{ route('campaigns.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition w-full sm:w-auto">
                Crear una campaña
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </a>
            <a href="{{ route('campaigns.index') }}" class="text-sm text-navy/60 hover:text-navy underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition py-2 -my-2">
                Ver campañas anteriores
            </a>
        </div>
    </section>

    {{-- Cómo funciona — editorial list --}}
    <section class="border-t border-navy/15 pt-12 sm:pt-14 pb-2 sm:pb-4">
        <div class="grid grid-cols-12 gap-x-6 sm:gap-x-8 gap-y-8">
            <header class="col-span-12 md:col-span-3">
                <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/40 mb-3">
                    Cómo funciona
                </p>
                <h2 class="font-[Fredoka] font-semibold text-3xl leading-[1.05] tracking-tight">
                    Cuatro agentes,<br>en orden.
                </h2>
                <p class="text-sm text-navy/55 mt-4 max-w-[16rem] leading-relaxed">
                    Cada uno recibe el contexto del anterior. Si uno se equivoca, el siguiente lo detecta antes de que llegue al final.
                </p>
            </header>

            <ol class="col-span-12 md:col-span-9 md:-mt-2">
                @foreach ([
                    ['Analista', 'Lee la base de clientes y los datos de reservas. Identifica segmentos, patrones y momentos. Decide a quién merece la pena apuntar.'],
                    ['Estratega', 'Toma el segmento elegido y le asigna un hotel, un canal y un momento. Define el mensaje clave y el tono que mejor encaja.'],
                    ['Creativo', 'Escribe el asunto, el preview, el cuerpo del email y el CTA. Propone formatos alternativos por si quieres probar A/B.'],
                    ['Auditor', 'Cruza la creatividad con la estrategia y los datos del análisis. Si algo no encaja, lo señala. Devuelve un score de calidad.'],
                ] as $i => [$name, $desc])
                    <li class="grid grid-cols-12 gap-x-3 sm:gap-x-4 gap-y-1 py-6 sm:py-7 border-b border-navy/10 last:border-0">
                        <span class="col-span-2 md:col-span-1 font-mono text-xs text-navy/35 pt-1 sm:pt-1.5">
                            0{{ $i + 1 }}
                        </span>
                        <h3 class="col-span-10 md:col-span-3 font-[Fredoka] font-semibold text-lg sm:text-xl">
                            {{ $name }}
                        </h3>
                        <p class="col-span-12 md:col-start-5 md:col-span-8 text-navy/65 leading-relaxed text-[15px] sm:text-base">
                            {{ $desc }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.app>
