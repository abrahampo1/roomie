<x-layouts.app title="Nueva campaña">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Nueva campaña</h1>
            <p class="text-navy/50 mt-2">Describe tu objetivo de negocio y los 4 agentes de IA harán el resto.</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-sand p-4">
                <p class="text-2xl font-bold">{{ $hotelCount }}</p>
                <p class="text-sm text-navy/50">Hoteles en catálogo</p>
            </div>
            <div class="bg-white rounded-xl border border-sand p-4">
                <p class="text-2xl font-bold">{{ $customerCount }}</p>
                <p class="text-sm text-navy/50">Clientes con datos</p>
            </div>
        </div>

        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="objective" class="block text-sm font-medium mb-2">
                    Objetivo de negocio
                </label>
                <textarea
                    name="objective"
                    id="objective"
                    rows="4"
                    required
                    minlength="10"
                    class="w-full rounded-xl border border-sand bg-white px-4 py-3 text-navy placeholder:text-navy/30 focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy/40 transition"
                    placeholder="Ej: Aumentar la ocupación del Aurea Catedral en Granada durante junio, dirigiéndome a parejas internacionales que buscan escapadas culturales..."
                >{{ old('objective') }}</textarea>
                @error('objective')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sugerencias --}}
            <div>
                <p class="text-xs text-navy/40 mb-2">Ideas de objetivos:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'Subir ocupación del Eurostars Torre Sevilla en temporada baja',
                        'Captar clientes italianos para hoteles en Lisboa y Oporto',
                        'Reactivar clientes que no reservan desde hace más de 6 meses',
                        'Promover la Isla de la Toja como destino de bienestar en verano',
                    ] as $suggestion)
                        <button
                            type="button"
                            onclick="document.getElementById('objective').value = this.textContent.trim()"
                            class="text-xs bg-sand-light border border-sand px-3 py-1.5 rounded-lg hover:bg-sand transition cursor-pointer"
                        >{{ $suggestion }}</button>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-navy text-sand-light py-3 rounded-xl font-medium hover:bg-navy-light transition shadow-lg shadow-navy/10">
                Lanzar pipeline de IA
            </button>
        </form>
    </div>
</x-layouts.app>
