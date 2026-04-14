<x-layouts.dashboard title="Nueva campaña" active="campaigns">
    <div class="max-w-2xl" x-data="{
        step: 1,
        name: '{{ old('name', '') }}',
        objective: '{{ old('objective', '') }}',
        aggressiveness: {{ old('aggressiveness', 2) }},
        persuasionPatterns: {{ old('persuasion_patterns', 2) }},
        provider: '{{ old('provider', 'anthropic') }}',
        apiKey: '',
        apiBaseUrl: '{{ old('api_base_url', '') }}',
        apiModel: '{{ old('api_model', '') }}',

        aggLabels: ['Informativa', 'Invitación', 'Equilibrada', 'Persuasiva', 'Insistente', 'Agresiva'],
        patLabels: ['Neutral', 'Sutil', 'Con urgencia', 'Con FOMO', 'Con presión', 'Dark patterns'],

        placeholders: {
            anthropic: 'sk-ant-...',
            google: 'AIza...',
            openai: 'sk-proj-...',
            deepseek: 'sk-...',
            custom: 'sk-...',
        },
        docs: {
            anthropic: 'https://console.anthropic.com/settings/keys',
            google: 'https://aistudio.google.com/apikey',
            openai: 'https://platform.openai.com/api-keys',
            deepseek: 'https://platform.deepseek.com/api_keys',
            custom: null,
        },

        canAdvance() {
            if (this.step === 1) return this.objective.trim().length >= 10;
            if (this.step === 2) return true;
            if (this.step === 3) {
                if (!this.apiKey.trim()) return false;
                if (this.provider === 'custom' && (!this.apiBaseUrl.trim() || !this.apiModel.trim())) return false;
                return true;
            }
            return true;
        },
        next() { if (this.canAdvance() && this.step < 4) this.step++; },
        prev() { if (this.step > 1) this.step--; },
        goTo(n) { this.step = n; },

        init() {
            const storageKey = (p) => 'roomie:llm-key:' + p;
            try {
                const saved = localStorage.getItem(storageKey(this.provider));
                if (saved) this.apiKey = saved;
                if (this.provider === 'custom') {
                    this.apiBaseUrl = this.apiBaseUrl || localStorage.getItem('roomie:llm-url:custom') || '';
                    this.apiModel = this.apiModel || localStorage.getItem('roomie:llm-model:custom') || '';
                }
            } catch (e) {}

            this.$watch('provider', (p) => {
                try {
                    this.apiKey = localStorage.getItem(storageKey(p)) || '';
                    if (p === 'custom') {
                        this.apiBaseUrl = this.apiBaseUrl || localStorage.getItem('roomie:llm-url:custom') || '';
                        this.apiModel = this.apiModel || localStorage.getItem('roomie:llm-model:custom') || '';
                    }
                } catch (e) {}
            });
        },
        persistKeys() {
            try {
                localStorage.setItem('roomie:llm-key:' + this.provider, this.apiKey);
                if (this.provider === 'custom') {
                    if (this.apiBaseUrl) localStorage.setItem('roomie:llm-url:custom', this.apiBaseUrl);
                    if (this.apiModel) localStorage.setItem('roomie:llm-model:custom', this.apiModel);
                }
            } catch (e) {}
        },
    }">
        <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Campañas
        </a>

        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight mt-3 sm:mt-4 mb-3">
            Nueva campaña
        </h1>

        {{-- ═══ Progress bar ═══ --}}
        <div class="flex items-center gap-0 mb-10 sm:mb-12">
            <template x-for="(s, i) in [{n:1, l:'Objetivo'}, {n:2, l:'Tono'}, {n:3, l:'Modelo'}, {n:4, l:'Resumen'}]" :key="s.n">
                <div class="flex items-center" :class="i < 3 ? 'flex-1' : ''">
                    <button
                        @click="s.n < step ? goTo(s.n) : null"
                        :class="step >= s.n ? 'bg-navy text-cream' : 'bg-navy/10 text-navy/40'"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-mono font-semibold transition shrink-0"
                        :disabled="s.n > step"
                        x-text="s.n"
                    ></button>
                    <span
                        class="text-[11px] ml-2 hidden sm:inline transition"
                        :class="step >= s.n ? 'text-navy font-medium' : 'text-navy/40'"
                        x-text="s.l"
                    ></span>
                    <div x-show="i < 3" class="flex-1 h-px mx-3" :class="step > s.n ? 'bg-navy' : 'bg-navy/10'"></div>
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('campaigns.store') }}" @submit="persistKeys()">
            @csrf

            {{-- Hidden fields to ensure all values are submitted --}}
            <input type="hidden" name="name" :value="name">
            <input type="hidden" name="objective" :value="objective">
            <input type="hidden" name="aggressiveness" :value="aggressiveness">
            <input type="hidden" name="persuasion_patterns" :value="persuasionPatterns">
            <input type="hidden" name="provider" :value="provider">
            <input type="hidden" name="api_key" :value="apiKey">
            <input type="hidden" name="api_base_url" :value="provider === 'custom' ? apiBaseUrl : ''">
            <input type="hidden" name="api_model" :value="provider === 'custom' ? apiModel : ''">

            {{-- ═══ Step 1: Objective ═══ --}}
            <section x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                    Cuéntale al pipeline qué quieres conseguir. Cuanto más concreto seas — qué hotel, qué público, qué momento del año — mejor será el resultado.
                </p>

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium mb-2">
                            Nombre <span class="text-navy/40 font-normal text-xs ml-1">opcional</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            x-model="name"
                            maxlength="120"
                            autocomplete="off"
                            autocapitalize="sentences"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            placeholder="Ej. Junio cultural en Granada"
                        >
                        <p class="text-xs text-navy/45 mt-1.5">Si lo dejas vacío, usaremos el que proponga el Estratega.</p>
                    </div>

                    <div>
                        <label for="objective" class="block text-sm font-medium mb-2">Objetivo</label>
                        <textarea
                            id="objective"
                            x-model="objective"
                            rows="5"
                            autocapitalize="sentences"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition resize-y leading-relaxed min-h-[140px]"
                            placeholder="Ej: Aumentar la ocupación del Aurea Catedral en Granada durante junio, dirigiéndome a parejas internacionales que buscan escapadas culturales..."
                        ></textarea>
                        <p class="text-xs text-navy/40 mt-1" x-show="objective.trim().length > 0 && objective.trim().length < 10">
                            Mínimo 10 caracteres
                        </p>
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
                                    @click="objective = '{{ $suggestion }}'"
                                    class="flex items-start gap-3 text-left text-[15px] sm:text-sm text-navy/65 hover:text-navy active:text-navy w-full group transition cursor-pointer py-2 -mx-2 px-2 rounded-lg active:bg-navy/[0.04]"
                                >
                                    <span class="text-copper mt-0.5 shrink-0">→</span>
                                    <span class="border-b border-navy/0 group-hover:border-navy/30 transition leading-relaxed">{{ $suggestion }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- ═══ Step 2: Tone ═══ --}}
            <section x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                    Cuánto presionas al cliente y qué trucos psicológicos permites. Los agentes adaptarán estrategia, copy y tono a estos niveles.
                </p>

                <div class="space-y-8">
                    {{-- Aggressiveness --}}
                    <div>
                        <div class="flex items-baseline justify-between gap-3 mb-2">
                            <label class="text-sm font-medium">Agresividad</label>
                            <span class="text-xs text-navy/55 font-mono tabular-nums">
                                <span x-text="aggressiveness">2</span>/5 · <span class="text-navy" x-text="aggLabels[aggressiveness]">Equilibrada</span>
                            </span>
                        </div>
                        <input
                            type="range"
                            min="0" max="5" step="1"
                            x-model.number="aggressiveness"
                            class="w-full h-1.5 bg-navy/10 rounded-full appearance-none cursor-pointer accent-copper"
                        >
                        <div class="flex justify-between text-[10px] text-navy/35 mt-1.5 font-mono uppercase tracking-wider">
                            <span>Informativa</span>
                            <span>Agresiva</span>
                        </div>
                    </div>

                    {{-- Persuasion patterns --}}
                    <div>
                        <div class="flex items-baseline justify-between gap-3 mb-2">
                            <label class="text-sm font-medium">Patrones de persuasión</label>
                            <span class="text-xs text-navy/55 font-mono tabular-nums">
                                <span x-text="persuasionPatterns">2</span>/5 · <span class="text-navy" x-text="patLabels[persuasionPatterns]">Con urgencia</span>
                            </span>
                        </div>
                        <input
                            type="range"
                            min="0" max="5" step="1"
                            x-model.number="persuasionPatterns"
                            class="w-full h-1.5 bg-navy/10 rounded-full appearance-none cursor-pointer accent-copper"
                        >
                        <div class="flex justify-between text-[10px] text-navy/35 mt-1.5 font-mono uppercase tracking-wider">
                            <span>Neutral</span>
                            <span>Dark patterns</span>
                        </div>
                    </div>

                    {{-- Preview card --}}
                    <div class="bg-sand-light/60 border border-navy/10 rounded-xl p-4 mt-6">
                        <p class="text-xs text-navy/45 mb-2 font-mono uppercase tracking-wider">Preview del tono</p>
                        <p class="text-sm text-navy/70 leading-relaxed" x-show="aggressiveness <= 1">
                            Tono informativo y amable. El email presenta opciones sin presión ni urgencia.
                        </p>
                        <p class="text-sm text-navy/70 leading-relaxed" x-show="aggressiveness === 2">
                            Tono equilibrado. Persuasión sutil con datos concretos y urgencia natural.
                        </p>
                        <p class="text-sm text-navy/70 leading-relaxed" x-show="aggressiveness === 3">
                            Tono persuasivo. Urgencia clara, beneficios destacados, llamada a la acción directa.
                        </p>
                        <p class="text-sm text-navy/70 leading-relaxed" x-show="aggressiveness >= 4">
                            Tono agresivo. Máxima presión, escasez explícita, urgencia extrema.
                        </p>
                    </div>
                </div>
            </section>

            {{-- ═══ Step 3: Model & API key ═══ --}}
            <section x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                    Elige qué modelo de IA ejecutará el pipeline. Necesitas una API key propia.
                </p>

                <div class="space-y-6">
                    {{-- Provider selector --}}
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
                                            :name="'_provider_ui'"
                                            value="{{ $value }}"
                                            class="peer sr-only"
                                            x-model="provider"
                                        >
                                        <span class="block px-3.5 py-2 text-xs font-medium rounded-xl text-navy/55 peer-checked:bg-navy peer-checked:text-cream transition whitespace-nowrap">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Custom fields --}}
                    <div x-show="provider === 'custom'" x-cloak class="space-y-4 border-l-2 border-copper/40 pl-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Base URL</label>
                            <input
                                type="url"
                                x-model="apiBaseUrl"
                                autocomplete="off"
                                autocapitalize="none"
                                autocorrect="off"
                                spellcheck="false"
                                inputmode="url"
                                class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                placeholder="https://api.together.xyz/v1"
                            >
                            <p class="text-xs text-navy/45 mt-1.5">Endpoint compatible con la API de OpenAI (chat/completions).</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Modelo</label>
                            <input
                                type="text"
                                x-model="apiModel"
                                autocomplete="off"
                                autocapitalize="none"
                                autocorrect="off"
                                spellcheck="false"
                                class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                placeholder="meta-llama/Llama-3.3-70B-Instruct-Turbo"
                            >
                        </div>
                    </div>

                    {{-- API key --}}
                    <div>
                        <label class="block text-sm font-medium mb-2">Tu API key</label>
                        <input
                            type="password"
                            x-model="apiKey"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            :placeholder="placeholders[provider] || 'sk-...'"
                        >
                        <p class="text-xs text-navy/50 mt-2 leading-relaxed max-w-xl">
                            La clave se guarda en tu navegador y solo se envía al servidor para esta campaña.
                            <a
                                x-show="docs[provider]"
                                :href="docs[provider]"
                                target="_blank"
                                rel="noopener"
                                class="underline underline-offset-2 decoration-navy/30 hover:decoration-navy"
                            >Conseguir una clave</a>
                        </p>
                    </div>
                </div>
            </section>

            {{-- ═══ Step 4: Summary ═══ --}}
            <section x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                    Revisa la configuración antes de lanzar el pipeline. Los 4 agentes trabajarán durante ~45 segundos.
                </p>

                <div class="bg-white border border-navy/15 rounded-2xl overflow-hidden">
                    {{-- Objective --}}
                    <div class="px-5 py-4 border-b border-navy/10">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-xs text-navy/45 font-mono uppercase tracking-wider">Objetivo</p>
                            <button type="button" @click="goTo(1)" class="text-[11px] text-copper hover:text-copper-dark transition">Editar</button>
                        </div>
                        <p class="text-sm text-navy/80 mt-2 leading-relaxed" x-text="objective"></p>
                        <p x-show="name" class="text-xs text-navy/45 mt-2">
                            Nombre: <span class="text-navy" x-text="name"></span>
                        </p>
                    </div>

                    {{-- Tone --}}
                    <div class="px-5 py-4 border-b border-navy/10">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-xs text-navy/45 font-mono uppercase tracking-wider">Tono</p>
                            <button type="button" @click="goTo(2)" class="text-[11px] text-copper hover:text-copper-dark transition">Editar</button>
                        </div>
                        <div class="flex gap-6 mt-2">
                            <p class="text-sm text-navy/70">
                                Agresividad: <span class="font-medium text-navy" x-text="aggressiveness + '/5 · ' + aggLabels[aggressiveness]"></span>
                            </p>
                            <p class="text-sm text-navy/70">
                                Persuasión: <span class="font-medium text-navy" x-text="persuasionPatterns + '/5 · ' + patLabels[persuasionPatterns]"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Model --}}
                    <div class="px-5 py-4">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-xs text-navy/45 font-mono uppercase tracking-wider">Modelo IA</p>
                            <button type="button" @click="goTo(3)" class="text-[11px] text-copper hover:text-copper-dark transition">Editar</button>
                        </div>
                        <p class="text-sm text-navy/80 mt-2 font-mono">
                            <span x-text="provider"></span>
                            <span x-show="provider === 'custom' && apiModel" x-text="' · ' + apiModel"></span>
                        </p>
                    </div>
                </div>

                <p class="text-xs text-navy/45 font-mono mt-6">
                    {{ $hotelCount }} hoteles · {{ $customerCount }} clientes en base de datos
                </p>
            </section>

            {{-- ═══ Navigation ═══ --}}
            <div class="mt-10 flex items-center justify-between gap-4">
                <button
                    type="button"
                    @click="prev()"
                    x-show="step > 1"
                    class="text-sm text-navy/55 hover:text-navy transition flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Anterior
                </button>
                <div x-show="step === 1"></div>

                <button
                    type="button"
                    @click="next()"
                    x-show="step < 4"
                    :disabled="!canAdvance()"
                    class="bg-navy text-cream px-6 py-3 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Siguiente
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>

                <button
                    type="submit"
                    x-show="step === 4"
                    x-cloak
                    class="bg-navy text-cream px-6 py-3.5 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center justify-center gap-2 w-full sm:w-auto"
                >
                    Lanzar pipeline
                    <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </button>
            </div>

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mt-6 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-700">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    </div>
</x-layouts.dashboard>
