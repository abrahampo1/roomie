<x-layouts.app title="Nueva campaña">
    <div class="max-w-3xl mx-auto">
        {{-- ═══════════════════════ HEADER ═══════════════════════ --}}
        <section class="relative mb-10 pb-8 border-b-2 border-navy">
            <svg class="absolute top-0 right-2 w-14 h-14 text-copper animate-[spin_18s_linear_infinite]" viewBox="0 0 24 24">
                <use href="#roomie-sparkle"/>
            </svg>

            <a href="{{ route('campaigns.index') }}" class="inline-flex items-center gap-1 text-xs uppercase tracking-widest text-navy/50 hover:text-navy transition mb-5">
                &larr; Todas las campañas
            </a>
            <p class="text-xs uppercase tracking-[0.3em] text-copper font-bold mb-3">/ nuevo brief</p>
            <h1 class="font-[Fredoka] font-bold leading-[0.85] tracking-tighter text-[clamp(2.5rem,7vw,5rem)]">
                Cuenta.<br>
                <span class="text-outline-thick">La magia</span> va después.
            </h1>
        </section>

        {{-- ═══════════════════════ STATS ═══════════════════════ --}}
        <div class="grid grid-cols-2 gap-4 mb-10">
            <div class="relative bg-white rounded-2xl border-2 border-navy p-5 overflow-hidden shadow-[4px_4px_0_0_#1a1a2e]">
                <div class="absolute inset-0 opacity-[0.06] text-navy">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-dots)"/></svg>
                </div>
                <div class="relative">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-copper font-bold mb-1">/ catálogo</p>
                    <p class="text-4xl font-[Fredoka] font-bold leading-none">{{ $hotelCount }}</p>
                    <p class="text-sm text-navy/50 mt-2">Hoteles disponibles</p>
                </div>
            </div>
            <div class="relative bg-navy text-cream rounded-2xl border-2 border-navy p-5 overflow-hidden shadow-[4px_4px_0_0_#c8956c]">
                <div class="absolute inset-0 opacity-[0.1] text-cream">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
                </div>
                <div class="relative">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-copper font-bold mb-1">/ audiencia</p>
                    <p class="text-4xl font-[Fredoka] font-bold leading-none">{{ $customerCount }}</p>
                    <p class="text-sm text-cream/60 mt-2">Clientes con datos</p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════ FORM ═══════════════════════ --}}
        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-8">
            @csrf

            <div class="relative">
                <div class="flex items-baseline gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-copper">01 /</span>
                    <label for="objective" class="text-lg font-[Fredoka] font-semibold">
                        ¿Qué objetivo de negocio tienes?
                    </label>
                </div>
                <div class="relative rounded-2xl border-2 border-navy bg-white shadow-[4px_4px_0_0_#1a1a2e] focus-within:shadow-[2px_2px_0_0_#1a1a2e] focus-within:translate-x-[2px] focus-within:translate-y-[2px] transition-all">
                    <textarea
                        name="objective"
                        id="objective"
                        rows="5"
                        required
                        minlength="10"
                        class="w-full rounded-2xl bg-transparent px-5 py-4 text-navy placeholder:text-navy/30 focus:outline-none resize-none"
                        placeholder="Ej: Aumentar la ocupación del Aurea Catedral en Granada durante junio, dirigiéndome a parejas internacionales que buscan escapadas culturales..."
                    >{{ old('objective') }}</textarea>
                </div>
                @error('objective')
                    <p class="text-red-600 text-sm mt-2 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Sugerencias --}}
            <div>
                <div class="flex items-baseline gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-copper">02 /</span>
                    <p class="text-sm font-[Fredoka] font-semibold">O elige una idea rápida</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'Subir ocupación del Eurostars Torre Sevilla en temporada baja',
                        'Captar clientes italianos para hoteles en Lisboa y Oporto',
                        'Reactivar clientes que no reservan desde hace más de 6 meses',
                        'Promover la Isla de la Toja como destino de bienestar en verano',
                    ] as $suggestion)
                        <button
                            type="button"
                            onclick="document.getElementById('objective').value = this.textContent.trim(); document.getElementById('objective').focus();"
                            class="group inline-flex items-center gap-2 text-xs bg-sand-light border-2 border-navy px-3 py-2 rounded-xl hover:bg-copper hover:text-cream transition-colors cursor-pointer"
                        >
                            <svg class="w-3 h-3 text-copper group-hover:text-cream transition-colors" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="group relative w-full bg-navy text-cream py-5 rounded-2xl font-[Fredoka] font-bold text-xl border-2 border-navy shadow-[6px_6px_0_0_#c8956c] hover:shadow-[3px_3px_0_0_#c8956c] hover:translate-x-[3px] hover:translate-y-[3px] transition-all overflow-hidden">
                <div class="absolute inset-0 opacity-[0.08] text-cream">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
                </div>
                <span class="relative inline-flex items-center gap-3">
                    Lanzar pipeline de IA
                    <svg class="w-5 h-5 text-copper group-hover:rotate-180 transition-transform duration-500" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </span>
            </button>
        </form>
    </div>
</x-layouts.app>
