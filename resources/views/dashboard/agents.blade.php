<x-layouts.dashboard title="Agentes IA" active="agents">

    {{-- Header --}}
    <header class="pb-7 sm:pb-8 mb-2">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Pipeline</p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Agentes IA</h1>
        <p class="mt-2 text-sm text-navy/60 max-w-xl leading-relaxed">
            Personaliza los agentes del pipeline o crea nuevos con prompts personalizados.
        </p>
    </header>

    {{-- Built-in agents --}}
    <section class="mb-10 sm:mb-12">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Agentes del sistema</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($builtinAgents as $agent)
                <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                            @if ($agent['icon'] === 'chart')
                                <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            @elseif ($agent['icon'] === 'target')
                                <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            @elseif ($agent['icon'] === 'pen')
                                <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            @elseif ($agent['icon'] === 'shield')
                                <svg class="w-4.5 h-4.5 text-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            @else
                                <svg class="w-4.5 h-4.5 text-navy/50" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-[Fredoka] font-semibold text-navy">{{ $agent['name'] }}</h3>
                            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-wider">{{ $agent['role'] }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-navy/55 leading-relaxed">{{ $agent['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Custom agents --}}
    <section class="mb-10 sm:mb-12">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Agentes personalizados</p>

        @if ($agents->isEmpty())
            <div class="py-12 text-center border border-dashed border-navy/15 rounded-2xl">
                <svg class="w-5 h-5 text-navy/15 mx-auto mb-3" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                <p class="text-navy/55 text-sm">No has creado agentes personalizados todavia.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($agents as $agent)
                    <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 flex flex-col" x-data="{ editing: false }">
                        {{-- View mode --}}
                        <div x-show="!editing">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-[Fredoka] font-semibold text-navy">{{ $agent->name }}</h3>
                                    <p class="font-mono text-[10px] text-navy/40 uppercase tracking-wider">{{ $agent->role }}</p>
                                </div>
                                <span class="shrink-0 font-mono text-[10px] px-2 py-0.5 rounded-full {{ $agent->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-navy/5 text-navy/40' }}">
                                    {{ $agent->is_active ? 'activo' : 'inactivo' }}
                                </span>
                            </div>

                            <p class="text-sm text-navy/55 leading-relaxed line-clamp-3 mb-4">{{ Str::limit($agent->system_prompt, 160) }}</p>

                            <div class="mt-auto flex items-center gap-2">
                                <button @click="editing = true" type="button"
                                        class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                                    Editar
                                </button>
                                <form method="POST" action="{{ route('dashboard.agents.destroy', $agent) }}"
                                      onsubmit="return confirm('Eliminar este agente?')">
                                    @csrf
                                    <button type="submit"
                                            class="border border-navy/20 text-navy/60 px-5 py-2.5 rounded-full text-sm font-medium hover:border-navy/40 hover:text-navy transition">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Edit mode --}}
                        <form x-show="editing" x-cloak method="POST" action="{{ route('dashboard.agents.update', $agent) }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Nombre</label>
                                <input type="text" name="name" value="{{ $agent->name }}"
                                       class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                       autocapitalize="none" required>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Rol</label>
                                <select name="role"
                                        class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                                    <option value="analyst" {{ $agent->role === 'analyst' ? 'selected' : '' }}>Analista</option>
                                    <option value="strategist" {{ $agent->role === 'strategist' ? 'selected' : '' }}>Estratega</option>
                                    <option value="creative" {{ $agent->role === 'creative' ? 'selected' : '' }}>Creativo</option>
                                    <option value="auditor" {{ $agent->role === 'auditor' ? 'selected' : '' }}>Auditor</option>
                                    <option value="custom" {{ $agent->role === 'custom' ? 'selected' : '' }}>Personalizado</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Prompt del sistema</label>
                                <textarea name="system_prompt" rows="5"
                                          class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed"
                                          required>{{ $agent->system_prompt }}</textarea>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Schema de salida <span class="text-navy/25 normal-case">(opcional)</span></label>
                                <textarea name="output_schema" rows="3"
                                          class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed">{{ $agent->output_schema }}</textarea>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 text-sm text-navy/70">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $agent->is_active ? 'checked' : '' }}
                                           class="rounded border-navy/20 text-copper focus:ring-copper/30">
                                    Activo
                                </label>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit"
                                        class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                                    Guardar
                                </button>
                                <button @click="editing = false" type="button"
                                        class="border border-navy/20 text-navy/60 px-5 py-2.5 rounded-full text-sm font-medium hover:border-navy/40 hover:text-navy transition">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Create agent form --}}
    <section>
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Crear agente</p>

        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
            <form method="POST" action="{{ route('dashboard.agents.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="agent-name" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Nombre</label>
                        <input id="agent-name" type="text" name="name" value="{{ old('name') }}"
                               placeholder="Mi analista personalizado"
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                               autocapitalize="none" required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="agent-role" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Rol</label>
                        <select id="agent-role" name="role"
                                class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                required>
                            <option value="analyst" {{ old('role') === 'analyst' ? 'selected' : '' }}>Analista</option>
                            <option value="strategist" {{ old('role') === 'strategist' ? 'selected' : '' }}>Estratega</option>
                            <option value="creative" {{ old('role') === 'creative' ? 'selected' : '' }}>Creativo</option>
                            <option value="auditor" {{ old('role') === 'auditor' ? 'selected' : '' }}>Auditor</option>
                            <option value="custom" {{ old('role') === 'custom' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="agent-prompt" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Prompt del sistema</label>
                    <textarea id="agent-prompt" name="system_prompt" rows="8"
                              placeholder="Eres un agente especializado en..."
                              class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed"
                              required>{{ old('system_prompt') }}</textarea>
                    @error('system_prompt')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="agent-schema" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">
                        Schema de salida <span class="text-navy/25 normal-case">(opcional, JSON)</span>
                    </label>
                    <textarea id="agent-schema" name="output_schema" rows="4"
                              placeholder='{"analysis": {"segments": [...], "opportunities": [...]}}'
                              class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition font-mono text-sm leading-relaxed">{{ old('output_schema') }}</textarea>
                    @error('output_schema')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                        Crear agente
                    </button>
                </div>
            </form>
        </div>
    </section>

</x-layouts.dashboard>
