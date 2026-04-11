<x-layouts.app title="Nueva campaña">
    <div class="max-w-2xl">
        <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition">
            ← Campañas
        </a>

        <h1 class="font-[Fredoka] font-semibold text-4xl tracking-tight mt-4 mb-3">
            Nueva campaña
        </h1>
        <p class="text-navy/60 leading-relaxed mb-12 max-w-xl">
            Cuéntale al pipeline qué quieres conseguir. Cuanto más concreto seas — qué hotel, qué público, qué momento del año — mejor será el resultado.
        </p>

        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-9">
            @csrf

            <div>
                <label for="objective" class="block text-sm font-medium mb-2">Objetivo</label>
                <textarea
                    name="objective"
                    id="objective"
                    rows="5"
                    required
                    minlength="10"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition resize-none leading-relaxed"
                    placeholder="Ej: Aumentar la ocupación del Aurea Catedral en Granada durante junio, dirigiéndome a parejas internacionales que buscan escapadas culturales..."
                >{{ old('objective') }}</textarea>
                @error('objective')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <p class="text-xs text-navy/45 mb-3">¿Sin ideas? Prueba con una de estas:</p>
                <div class="space-y-2">
                    @foreach ([
                        'Subir ocupación del Eurostars Torre Sevilla en temporada baja',
                        'Captar clientes italianos para hoteles en Lisboa y Oporto',
                        'Reactivar clientes que no reservan desde hace más de 6 meses',
                        'Promover la Isla de la Toja como destino de bienestar en verano',
                    ] as $suggestion)
                        <button
                            type="button"
                            onclick="const t = document.getElementById('objective'); t.value = this.querySelector('span:last-child').textContent.trim(); t.focus();"
                            class="flex items-start gap-3 text-left text-sm text-navy/65 hover:text-navy w-full group transition cursor-pointer"
                        >
                            <span class="text-copper mt-px shrink-0">→</span>
                            <span class="border-b border-navy/0 group-hover:border-navy/30 transition leading-relaxed">{{ $suggestion }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-navy/10 flex items-center justify-between gap-4 flex-wrap">
                <p class="text-xs text-navy/45 font-mono">
                    {{ $hotelCount }} hoteles · {{ $customerCount }} clientes en base de datos
                </p>
                <button type="submit" class="bg-navy text-cream pl-6 pr-5 py-3 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center gap-2">
                    Lanzar pipeline
                    <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
