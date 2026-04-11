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

            <div class="pt-8 border-t border-navy/10 space-y-6">
                <div>
                    <p class="text-sm font-medium mb-2">Modelo</p>
                    <div class="inline-flex border border-navy/20 rounded-full p-1 bg-white">
                        @foreach ([
                            'anthropic' => 'Anthropic Claude',
                            'google' => 'Google Gemini',
                        ] as $value => $label)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="provider"
                                    value="{{ $value }}"
                                    class="peer sr-only"
                                    {{ old('provider', 'anthropic') === $value ? 'checked' : '' }}
                                >
                                <span class="block px-4 py-1.5 text-xs font-medium rounded-full text-navy/55 peer-checked:bg-navy peer-checked:text-cream transition">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('provider')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="api_key" class="block text-sm font-medium mb-2">Tu API key</label>
                    <input
                        type="password"
                        name="api_key"
                        id="api_key"
                        required
                        autocomplete="off"
                        spellcheck="false"
                        class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-sm text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                        placeholder="sk-ant-..."
                    >
                    <p class="text-xs text-navy/50 mt-2 leading-relaxed max-w-xl">
                        La clave se guarda en tu navegador y solo se envía al servidor para esta campaña. Se borra de la base de datos en cuanto el pipeline termina.
                        <a id="provider-link" href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener" class="underline underline-offset-2 decoration-navy/30 hover:decoration-navy">Conseguir una clave</a>.
                    </p>
                    @error('api_key')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-4 flex-wrap">
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

    @push('scripts')
    <script>
        (function () {
            const placeholders = {
                anthropic: 'sk-ant-...',
                google: 'AIza...',
            };
            const docs = {
                anthropic: 'https://console.anthropic.com/settings/keys',
                google: 'https://aistudio.google.com/apikey',
            };
            const storageKey = (provider) => 'roomie:llm-key:' + provider;

            const keyInput = document.getElementById('api_key');
            const link = document.getElementById('provider-link');
            const radios = document.querySelectorAll('input[name="provider"]');
            const form = keyInput.closest('form');

            function applyProvider(provider) {
                keyInput.placeholder = placeholders[provider] ?? '';
                if (link) link.href = docs[provider] ?? '#';
                try {
                    keyInput.value = localStorage.getItem(storageKey(provider)) || '';
                } catch (e) {
                    keyInput.value = '';
                }
            }

            radios.forEach((radio) => {
                radio.addEventListener('change', (e) => applyProvider(e.target.value));
            });

            const checked = document.querySelector('input[name="provider"]:checked');
            if (checked) applyProvider(checked.value);

            form.addEventListener('submit', () => {
                const provider = document.querySelector('input[name="provider"]:checked')?.value;
                if (provider && keyInput.value) {
                    try {
                        localStorage.setItem(storageKey(provider), keyInput.value);
                    } catch (e) {
                        // ignore quota / private mode errors
                    }
                }
            });
        })();
    </script>
    @endpush
</x-layouts.app>
