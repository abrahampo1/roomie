<x-layouts.app title="Home">
    <div class="flex flex-col items-center justify-center min-h-[70vh] text-center">
        <div class="w-16 h-16 rounded-2xl bg-navy flex items-center justify-center mb-8">
            <svg class="w-9 h-9 text-sand-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
            </svg>
        </div>

        <h1 class="text-5xl font-bold tracking-tight mb-4">
            Make Me Want to Travel
        </h1>
        <p class="text-lg text-navy/60 max-w-xl mb-10 leading-relaxed">
            Campañas de marketing hotelero hiper-personalizadas,
            diseñadas por 4 agentes de IA que analizan, estrategizan, crean y auditan.
        </p>

        <div class="flex gap-4">
            <a href="{{ route('campaigns.create') }}"
               class="bg-navy text-sand-light px-6 py-3 rounded-xl font-medium hover:bg-navy-light transition shadow-lg shadow-navy/10">
                Crear campaña
            </a>
            <a href="{{ route('campaigns.index') }}"
               class="border border-navy/20 px-6 py-3 rounded-xl font-medium hover:bg-navy/5 transition">
                Ver campañas
            </a>
        </div>

        {{-- Pipeline visual --}}
        <div class="mt-20 grid grid-cols-4 gap-4 max-w-3xl w-full">
            @foreach ([
                ['Analista', 'Segmenta clientes y extrae insights de los datos', 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5'],
                ['Estratega', 'Define público, hotel, timing y canal óptimos', 'M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z'],
                ['Creativo', 'Genera copy, asunto, CTA y formatos alternativos', 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42'],
                ['Auditor', 'Revisa coherencia y da score de calidad final', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $i => [$name, $desc, $icon])
                <div class="bg-white rounded-xl border border-sand p-5 text-left">
                    <div class="w-10 h-10 rounded-lg bg-sand-light flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                        </svg>
                    </div>
                    <p class="font-semibold text-sm mb-1">{{ $name }}</p>
                    <p class="text-xs text-navy/50 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
