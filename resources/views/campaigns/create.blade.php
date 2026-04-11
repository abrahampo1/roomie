<x-layouts.app title="Nueva campaña">
    <div class="max-w-2xl">
        <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Campañas
        </a>

        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight mt-3 sm:mt-4 mb-3">
            Nueva campaña
        </h1>
        <p class="text-navy/60 leading-relaxed mb-10 sm:mb-12 max-w-xl">
            Cuéntale al pipeline qué quieres conseguir. Cuanto más concreto seas — qué hotel, qué público, qué momento del año — mejor será el resultado.
        </p>

        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-8 sm:space-y-9">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium mb-2">
                    Nombre <span class="text-navy/40 font-normal text-xs ml-1">opcional</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    maxlength="120"
                    autocomplete="off"
                    autocapitalize="sentences"
                    value="{{ old('name') }}"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="Ej. Junio cultural en Granada"
                >
                <p class="text-xs text-navy/45 mt-1.5">Si lo dejas vacío, usaremos el que proponga el Estratega.</p>
                @error('name')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="objective" class="block text-sm font-medium mb-2">Objetivo</label>
                <textarea
                    name="objective"
                    id="objective"
                    rows="5"
                    required
                    minlength="10"
                    autocapitalize="sentences"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition resize-y leading-relaxed min-h-[140px]"
                    placeholder="Ej: Aumentar la ocupación del Aurea Catedral en Granada durante junio, dirigiéndome a parejas internacionales que buscan escapadas culturales..."
                >{{ old('objective') }}</textarea>
                @error('objective')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <p class="text-xs text-navy/45 mb-3">¿Sin ideas? Prueba con una de estas:</p>
                <div class="space-y-1">
                    @foreach ([
                        'Subir ocupación del Eurostars Torre Sevilla en temporada baja',
                        'Captar clientes italianos para hoteles en Lisboa y Oporto',
                        'Reactivar clientes que no reservan desde hace más de 6 meses',
                        'Promover la Isla de la Toja como destino de bienestar en verano',
                    ] as $suggestion)
                        <button
                            type="button"
                            onclick="const t = document.getElementById('objective'); t.value = this.querySelector('span:last-child').textContent.trim(); t.focus();"
                            class="flex items-start gap-3 text-left text-[15px] sm:text-sm text-navy/65 hover:text-navy active:text-navy w-full group transition cursor-pointer py-2 -mx-2 px-2 rounded-lg active:bg-navy/[0.04]"
                        >
                            <span class="text-copper mt-0.5 shrink-0">→</span>
                            <span class="border-b border-navy/0 group-hover:border-navy/30 transition leading-relaxed">{{ $suggestion }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pt-8 border-t border-navy/10 space-y-6">
                <div>
                    <p class="text-sm font-medium mb-3">Modelo</p>
                    <div class="-mx-1 px-1 overflow-x-auto">
                        <div class="inline-flex gap-1 border border-navy/20 rounded-2xl p-1 bg-white">
                            @foreach ([
                                'anthropic' => 'Anthropic',
                                'google' => 'Google',
                                'openai' => 'OpenAI',
                                'deepseek' => 'DeepSeek',
                                'custom' => 'Custom',
                            ] as $value => $label)
                                <label class="cursor-pointer shrink-0">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="{{ $value }}"
                                        class="peer sr-only"
                                        {{ old('provider', 'anthropic') === $value ? 'checked' : '' }}
                                    >
                                    <span class="block px-3.5 py-2 text-xs font-medium rounded-xl text-navy/55 peer-checked:bg-navy peer-checked:text-cream transition whitespace-nowrap">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @error('provider')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div id="custom-fields" hidden class="space-y-4 border-l-2 border-copper/40 pl-4">
                    <div>
                        <label for="api_base_url" class="block text-sm font-medium mb-2">Base URL</label>
                        <input
                            type="url"
                            name="api_base_url"
                            id="api_base_url"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            inputmode="url"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            placeholder="https://api.together.xyz/v1"
                            value="{{ old('api_base_url') }}"
                        >
                        <p class="text-xs text-navy/45 mt-1.5">Endpoint compatible con la API de OpenAI (chat/completions).</p>
                        @error('api_base_url')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="api_model" class="block text-sm font-medium mb-2">Modelo</label>
                        <input
                            type="text"
                            name="api_model"
                            id="api_model"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            placeholder="meta-llama/Llama-3.3-70B-Instruct-Turbo"
                            value="{{ old('api_model') }}"
                        >
                        @error('api_model')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="api_key" class="block text-sm font-medium mb-2">Tu API key</label>
                    <input
                        type="password"
                        name="api_key"
                        id="api_key"
                        required
                        autocomplete="off"
                        autocapitalize="none"
                        autocorrect="off"
                        spellcheck="false"
                        class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
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

            <div class="pt-2 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-xs text-navy/45 font-mono">
                    {{ $hotelCount }} hoteles · {{ $customerCount }} clientes en base de datos
                </p>
                <button type="submit" class="bg-navy text-cream px-6 py-3.5 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center justify-center gap-2 w-full sm:w-auto">
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
                openai: 'sk-proj-...',
                deepseek: 'sk-...',
                custom: 'sk-... (o cualquier valor si tu endpoint no requiere auth)',
            };
            const docs = {
                anthropic: 'https://console.anthropic.com/settings/keys',
                google: 'https://aistudio.google.com/apikey',
                openai: 'https://platform.openai.com/api-keys',
                deepseek: 'https://platform.deepseek.com/api_keys',
                custom: null,
            };

            const storageKey = (provider) => 'roomie:llm-key:' + provider;
            const storageUrl = 'roomie:llm-url:custom';
            const storageModel = 'roomie:llm-model:custom';

            const keyInput = document.getElementById('api_key');
            const link = document.getElementById('provider-link');
            const customFields = document.getElementById('custom-fields');
            const baseUrlInput = document.getElementById('api_base_url');
            const modelInput = document.getElementById('api_model');
            const radios = document.querySelectorAll('input[name="provider"]');
            const form = keyInput.closest('form');

            const safeGet = (k) => {
                try { return localStorage.getItem(k) || ''; } catch (e) { return ''; }
            };
            const safeSet = (k, v) => {
                try { localStorage.setItem(k, v); } catch (e) { /* private mode / quota */ }
            };

            function applyProvider(provider) {
                keyInput.placeholder = placeholders[provider] ?? '';
                keyInput.value = safeGet(storageKey(provider));

                if (link) {
                    if (docs[provider]) {
                        link.href = docs[provider];
                        link.hidden = false;
                    } else {
                        link.hidden = true;
                    }
                }

                const isCustom = provider === 'custom';
                customFields.hidden = !isCustom;
                baseUrlInput.required = isCustom;
                modelInput.required = isCustom;

                if (isCustom) {
                    if (!baseUrlInput.value) baseUrlInput.value = safeGet(storageUrl);
                    if (!modelInput.value) modelInput.value = safeGet(storageModel);
                }
            }

            radios.forEach((radio) => {
                radio.addEventListener('change', (e) => applyProvider(e.target.value));
            });

            const checked = document.querySelector('input[name="provider"]:checked');
            if (checked) applyProvider(checked.value);

            form.addEventListener('submit', () => {
                const provider = document.querySelector('input[name="provider"]:checked')?.value;
                if (!provider) return;
                if (keyInput.value) safeSet(storageKey(provider), keyInput.value);
                if (provider === 'custom') {
                    if (baseUrlInput.value) safeSet(storageUrl, baseUrlInput.value);
                    if (modelInput.value) safeSet(storageModel, modelInput.value);
                }
            });
        })();
    </script>
    @endpush
</x-layouts.app>
