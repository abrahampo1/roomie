<x-layouts.dashboard title="Agentes IA" active="agents">

    {{-- Header --}}
    <header class="pb-7 sm:pb-8 mb-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-2">Pipeline</p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Agentes IA</h1>
        <p class="mt-2 text-sm text-navy/55 max-w-xl">Personaliza los agentes del pipeline o crea nuevos a partir de plantillas.</p>
    </header>

    {{-- Built-in agents --}}
    <section class="mb-10">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Agentes del sistema</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($builtinAgents as $agent)
                <div class="rounded-2xl border border-navy/10 bg-white p-5">
                    <div class="w-9 h-9 rounded-xl bg-navy/5 flex items-center justify-center mb-3">
                        @if ($agent['icon'] === 'chart')
                            <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        @elseif ($agent['icon'] === 'target')
                            <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        @elseif ($agent['icon'] === 'pen')
                            <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                        @elseif ($agent['icon'] === 'shield')
                            <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        @else
                            <svg class="w-4.5 h-4.5 text-navy/50" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        @endif
                    </div>
                    <h3 class="font-[Fredoka] font-semibold text-navy">{{ $agent['name'] }}</h3>
                    <p class="font-mono text-[10px] text-navy/35 uppercase tracking-wider">{{ $agent['role'] }}</p>
                    <p class="text-sm text-navy/50 leading-relaxed mt-2">{{ $agent['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Create agent --}}
    <section class="mb-10">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Crear nuevo agente</p>

        {{-- Template cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 mb-6" id="agent-templates">
            @php
                $templates = [
                    ['name' => 'Analista de competencia', 'role' => 'analyst', 'desc' => 'Investiga la competencia del hotel y encuentra oportunidades.', 'prompt' => "Eres un analista de inteligencia competitiva hotelera. Analiza la competencia del hotel objetivo, identifica fortalezas y debilidades, y encuentra oportunidades de diferenciación.\n\nResponde con un JSON: competitors, opportunities, threats, recommended_positioning.\n\nResponde SOLO el JSON."],
                    ['name' => 'Generador de asuntos', 'role' => 'creative', 'desc' => 'Genera 10 variantes de subject line para open rate.', 'prompt' => "Eres un especialista en subject lines de email marketing hotelero. Genera variantes de asuntos para maximizar el open rate.\n\nResponde con un JSON: variants (array de 10 objetos con subject_line, preview_text, technique, expected_open_rate).\n\nResponde SOLO el JSON."],
                    ['name' => 'Traductor multicultural', 'role' => 'creative', 'desc' => 'Adapta el copy a diferentes mercados y culturas.', 'prompt' => "Eres un experto en localización de marketing hotelero. Adapta el copy de un email a un mercado específico manteniendo la esencia.\n\nResponde con un JSON: adapted_subject_line, adapted_headline, adapted_body_html, cultural_notes, market.\n\nResponde SOLO el JSON."],
                    ['name' => 'Segmentador avanzado', 'role' => 'analyst', 'desc' => 'Crea micro-segmentos para personalización granular.', 'prompt' => "Eres un analista de segmentación de clientes hoteleros. Crea micro-segmentos a partir de datos de clientes.\n\nResponde con un JSON: micro_segments (array con name, size, profile, value_score, recommended_approach, key_triggers).\n\nResponde SOLO el JSON."],
                    ['name' => 'Auditor de accesibilidad', 'role' => 'auditor', 'desc' => 'Revisa accesibilidad y compatibilidad del email.', 'prompt' => "Eres un auditor de accesibilidad de emails. Revisa un email HTML para garantizar accesibilidad y compatibilidad.\n\nResponde con un JSON: accessibility_score, email_client_compatibility, issues, improvements, final_verdict.\n\nResponde SOLO el JSON."],
                    ['name' => 'Agente en blanco', 'role' => 'custom', 'desc' => 'Empieza desde cero con un prompt propio.', 'prompt' => ''],
                ];
            @endphp

            @foreach ($templates as $i => $tpl)
                <button type="button" data-template="{{ $i }}"
                        class="agent-tpl-btn text-left rounded-2xl border border-navy/10 bg-white p-5 hover:border-copper/40 hover:bg-copper/[0.02] transition group cursor-pointer">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-copper/10 flex items-center justify-center shrink-0 group-hover:bg-copper/20 transition">
                            <svg class="w-4 h-4 text-copper" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-sm text-navy group-hover:text-copper-dark transition">{{ $tpl['name'] }}</p>
                            <p class="text-xs text-navy/45 mt-1 leading-relaxed">{{ $tpl['desc'] }}</p>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        {{-- Create form --}}
        <div id="agent-form-wrapper" class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 hidden">
            <div class="flex items-center justify-between mb-5">
                <p class="font-[Fredoka] font-semibold text-navy">Nuevo agente</p>
                <button type="button" id="agent-form-close" class="text-navy/40 hover:text-navy transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('dashboard.agents.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="agent-name" class="block text-xs text-navy/55 mb-1.5">Nombre</label>
                        <input id="agent-name" type="text" name="name" required
                               placeholder="Mi agente"
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="agent-role" class="block text-xs text-navy/55 mb-1.5">Rol en el pipeline</label>
                        <select id="agent-role" name="role" required
                                class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition select-styled">
                            <option value="analyst">Analista</option>
                            <option value="strategist">Estratega</option>
                            <option value="creative">Creativo</option>
                            <option value="auditor">Auditor</option>
                            <option value="custom">Personalizado</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="agent-prompt" class="block text-xs text-navy/55 mb-1.5">Prompt del sistema</label>
                    <textarea id="agent-prompt" name="system_prompt" rows="8" required
                              placeholder="Eres un agente especializado en..."
                              class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed placeholder:font-sans placeholder:text-navy/30"></textarea>
                    <p class="text-[11px] text-navy/35 mt-1">El prompt debe terminar pidiendo respuesta en JSON.</p>
                    @error('system_prompt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <details class="group">
                    <summary class="text-xs text-navy/45 hover:text-navy transition cursor-pointer list-none flex items-center gap-1.5">
                        <svg class="w-3 h-3 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        Opciones avanzadas
                    </summary>
                    <div class="mt-3">
                        <label for="agent-schema" class="block text-xs text-navy/55 mb-1.5">Schema de salida JSON (opcional)</label>
                        <textarea id="agent-schema" name="output_schema" rows="4"
                                  placeholder="{ }"
                                  class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed placeholder:font-sans placeholder:text-navy/30"></textarea>
                    </div>
                </details>

                <button type="submit"
                        class="bg-navy text-cream px-6 py-3 rounded-full text-sm font-medium hover:bg-navy-light transition cursor-pointer">
                    Crear agente
                </button>
            </form>
        </div>
    </section>

    {{-- Custom agents --}}
    @if ($agents->isNotEmpty())
        <section>
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Mis agentes</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($agents as $agent)
                    <div class="rounded-2xl border border-navy/10 bg-white p-5 flex flex-col" x-data="{ editing: false }">
                        <div x-show="!editing">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <h3 class="font-[Fredoka] font-semibold text-navy">{{ $agent->name }}</h3>
                                    <p class="font-mono text-[10px] text-navy/35 uppercase tracking-wider">{{ $agent->role }}</p>
                                </div>
                                <span class="shrink-0 font-mono text-[10px] px-2 py-0.5 rounded-full {{ $agent->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-navy/5 text-navy/40' }}">
                                    {{ $agent->is_active ? 'activo' : 'inactivo' }}
                                </span>
                            </div>
                            <p class="text-xs text-navy/45 leading-relaxed line-clamp-3 mb-4">{{ Str::limit($agent->system_prompt, 150) }}</p>
                            <div class="mt-auto flex items-center gap-2">
                                <button @click="editing = true" type="button"
                                        class="text-xs text-navy/50 hover:text-navy transition underline underline-offset-4 decoration-navy/20 cursor-pointer">Editar</button>
                                <form method="POST" action="{{ route('dashboard.agents.destroy', $agent) }}" onsubmit="return confirm('¿Eliminar este agente?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-navy/40 hover:text-red-700 transition underline underline-offset-4 decoration-navy/20 cursor-pointer">Eliminar</button>
                                </form>
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('dashboard.agents.update', $agent) }}" class="space-y-3">
                            @csrf
                            <input type="text" name="name" value="{{ $agent->name }}" required class="w-full rounded-xl border border-navy/20 bg-white px-3 py-2.5 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                            <select name="role" class="w-full rounded-xl border border-navy/20 bg-white px-3 py-2.5 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition select-styled">
                                @foreach (['analyst' => 'Analista', 'strategist' => 'Estratega', 'creative' => 'Creativo', 'auditor' => 'Auditor', 'custom' => 'Personalizado'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $agent->role === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            <textarea name="system_prompt" rows="5" required class="w-full rounded-xl border border-navy/20 bg-white px-3 py-2.5 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono leading-relaxed">{{ $agent->system_prompt }}</textarea>
                            <label class="flex items-center gap-2 text-xs text-navy/60">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ $agent->is_active ? 'checked' : '' }} class="rounded border-navy/20 text-copper focus:ring-copper/30">
                                Activo
                            </label>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-navy text-cream px-4 py-2 rounded-full text-xs font-medium hover:bg-navy-light transition cursor-pointer">Guardar</button>
                                <button @click="editing = false" type="button" class="text-xs text-navy/50 hover:text-navy transition cursor-pointer">Cancelar</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @push('scripts')
    <script>
    (function() {
        var templates = @json($templates);
        var wrapper = document.getElementById('agent-form-wrapper');
        var templatesGrid = document.getElementById('agent-templates');
        var nameInput = document.getElementById('agent-name');
        var roleSelect = document.getElementById('agent-role');
        var promptArea = document.getElementById('agent-prompt');
        var closeBtn = document.getElementById('agent-form-close');

        document.querySelectorAll('.agent-tpl-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.template);
                var tpl = templates[idx];
                if (!tpl) return;

                nameInput.value = tpl.name;
                roleSelect.value = tpl.role;
                promptArea.value = tpl.prompt;

                templatesGrid.classList.add('hidden');
                wrapper.classList.remove('hidden');
                nameInput.focus();
            });
        });

        closeBtn.addEventListener('click', function() {
            wrapper.classList.add('hidden');
            templatesGrid.classList.remove('hidden');
        });
    })();
    </script>
    @endpush

</x-layouts.dashboard>
