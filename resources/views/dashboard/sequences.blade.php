<x-layouts.dashboard title="Secuencias" active="sequences">

    {{-- Header --}}
    <header class="pb-7 sm:pb-8 mb-2">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Pipeline</p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Secuencias</h1>
        <p class="mt-2 text-sm text-navy/60 max-w-xl leading-relaxed">
            Define el orden de ejecucion de los agentes en el pipeline.
        </p>
    </header>

    {{-- Default pipeline --}}
    <section class="mb-10 sm:mb-12">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Secuencia por defecto</p>

        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
            <div class="flex items-center gap-0 overflow-x-auto">
                @foreach ($builtinSteps as $i => $step)
                    <div class="flex items-center shrink-0">
                        <div class="flex items-center gap-2.5 bg-navy/5 rounded-xl px-4 py-3">
                            <span class="font-mono text-[10px] text-navy/35">{{ $i + 1 }}</span>
                            <span class="font-[Fredoka] font-semibold text-sm text-navy">{{ $step['label'] }}</span>
                            <span class="font-mono text-[10px] text-navy/30 uppercase">{{ $step['role'] }}</span>
                        </div>
                        @if ($i < count($builtinSteps) - 1)
                            <svg class="w-5 h-5 text-navy/20 mx-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- User sequences --}}
    <section class="mb-10 sm:mb-12">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Mis secuencias</p>

        @if ($sequences->isEmpty())
            <div class="py-12 text-center border border-dashed border-navy/15 rounded-2xl">
                <svg class="w-5 h-5 text-navy/15 mx-auto mb-3" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                <p class="text-navy/55 text-sm">No has creado secuencias personalizadas todavia.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($sequences as $sequence)
                    <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 flex flex-col" x-data="{ editing: false }">
                        {{-- View mode --}}
                        <div x-show="!editing">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-[Fredoka] font-semibold text-navy">{{ $sequence->name }}</h3>
                                    @if ($sequence->description)
                                        <p class="text-sm text-navy/55 mt-0.5">{{ $sequence->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($sequence->is_default)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded-full bg-copper/10 text-copper">
                                            por defecto
                                        </span>
                                    @endif
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded-full {{ $sequence->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-navy/5 text-navy/40' }}">
                                        {{ $sequence->is_active ? 'activa' : 'inactiva' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Steps inline flow --}}
                            @if (is_array($sequence->steps) && count($sequence->steps) > 0)
                                <div class="flex items-center gap-0 overflow-x-auto mb-4">
                                    @foreach ($sequence->steps as $si => $step)
                                        <div class="flex items-center shrink-0">
                                            <span class="bg-navy/5 rounded-lg px-3 py-1.5 text-xs font-medium text-navy/70">
                                                {{ $step['role'] ?? '?' }}
                                            </span>
                                            @if ($si < count($sequence->steps) - 1)
                                                <svg class="w-3.5 h-3.5 text-navy/15 mx-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <p class="font-mono text-[10px] text-navy/35 mb-4">{{ count($sequence->steps ?? []) }} pasos</p>

                            <div class="mt-auto flex items-center gap-2">
                                <button @click="editing = true" type="button"
                                        class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                                    Editar
                                </button>
                                <form method="POST" action="{{ route('dashboard.sequences.destroy', $sequence) }}"
                                      onsubmit="return confirm('Eliminar esta secuencia?')">
                                    @csrf
                                    <button type="submit"
                                            class="border border-navy/20 text-navy/60 px-5 py-2.5 rounded-full text-sm font-medium hover:border-navy/40 hover:text-navy transition">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Edit mode --}}
                        <form x-show="editing" x-cloak method="POST" action="{{ route('dashboard.sequences.update', $sequence) }}"
                              x-data="{
                                  steps: {{ Js::from($sequence->steps ?? []) }},
                                  addStep() { this.steps.push({ role: 'analyst', agent_id: null }) },
                                  removeStep(idx) { this.steps.splice(idx, 1) },
                                  moveUp(idx) { if (idx > 0) { [this.steps[idx], this.steps[idx-1]] = [this.steps[idx-1], this.steps[idx]] } },
                                  moveDown(idx) { if (idx < this.steps.length - 1) { [this.steps[idx], this.steps[idx+1]] = [this.steps[idx+1], this.steps[idx]] } },
                              }"
                              class="space-y-4">
                            @csrf

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Nombre</label>
                                <input type="text" name="name" value="{{ $sequence->name }}"
                                       class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                       required>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Descripcion</label>
                                <input type="text" name="description" value="{{ $sequence->description }}"
                                       class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                            </div>

                            {{-- Dynamic steps --}}
                            <div>
                                <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-2">Pasos</label>
                                <div class="space-y-2">
                                    <template x-for="(step, idx) in steps" :key="idx">
                                        <div class="flex items-center gap-2 bg-navy/[0.02] rounded-xl p-3">
                                            <span class="font-mono text-[10px] text-navy/30 w-5 text-center shrink-0" x-text="idx + 1"></span>

                                            <select :name="'steps[' + idx + '][role]'" x-model="step.role"
                                                    class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                                                <option value="analyst">Analista</option>
                                                <option value="strategist">Estratega</option>
                                                <option value="creative">Creativo</option>
                                                <option value="auditor">Auditor</option>
                                                <option value="custom">Personalizado</option>
                                            </select>

                                            <select :name="'steps[' + idx + '][agent_id]'" x-model="step.agent_id"
                                                    class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                                                <option value="">Agente del sistema</option>
                                                @foreach ($agents as $agent)
                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                @endforeach
                                            </select>

                                            <div class="flex items-center gap-1 shrink-0">
                                                <button type="button" @click="moveUp(idx)"
                                                        class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-navy/30 hover:text-navy/60 hover:border-navy/30 transition"
                                                        :disabled="idx === 0">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                    </svg>
                                                </button>
                                                <button type="button" @click="moveDown(idx)"
                                                        class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-navy/30 hover:text-navy/60 hover:border-navy/30 transition"
                                                        :disabled="idx === steps.length - 1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                                <button type="button" @click="removeStep(idx)"
                                                        class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-red-400 hover:text-red-600 hover:border-red-300 transition">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="addStep()"
                                        class="mt-2 text-sm text-navy/50 hover:text-navy underline underline-offset-4 decoration-navy/20 transition">
                                    Agregar paso
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 text-sm text-navy/70">
                                    <input type="hidden" name="is_default" value="0">
                                    <input type="checkbox" name="is_default" value="1" {{ $sequence->is_default ? 'checked' : '' }}
                                           class="rounded border-navy/20 text-copper focus:ring-copper/30">
                                    Secuencia por defecto
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

    {{-- Create sequence form --}}
    <section>
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Crear secuencia</p>

        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
            <form method="POST" action="{{ route('dashboard.sequences.store') }}"
                  x-data="{
                      steps: [
                          { role: 'analyst', agent_id: '' },
                          { role: 'strategist', agent_id: '' },
                          { role: 'creative', agent_id: '' },
                          { role: 'auditor', agent_id: '' },
                      ],
                      addStep() { this.steps.push({ role: 'analyst', agent_id: '' }) },
                      removeStep(idx) { this.steps.splice(idx, 1) },
                      moveUp(idx) { if (idx > 0) { [this.steps[idx], this.steps[idx-1]] = [this.steps[idx-1], this.steps[idx]] } },
                      moveDown(idx) { if (idx < this.steps.length - 1) { [this.steps[idx], this.steps[idx+1]] = [this.steps[idx+1], this.steps[idx]] } },
                  }"
                  class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="seq-name" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Nombre</label>
                        <input id="seq-name" type="text" name="name" value="{{ old('name') }}"
                               placeholder="Mi secuencia personalizada"
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                               required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seq-description" class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-1.5">Descripcion</label>
                        <input id="seq-description" type="text" name="description" value="{{ old('description') }}"
                               placeholder="Pipeline optimizado para..."
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dynamic step builder --}}
                <div>
                    <label class="block font-mono text-[10px] text-navy/40 uppercase tracking-wider mb-2">Pasos del pipeline</label>

                    <div class="space-y-2">
                        <template x-for="(step, idx) in steps" :key="idx">
                            <div class="flex items-center gap-2 bg-navy/[0.02] rounded-xl p-3">
                                <span class="font-mono text-[10px] text-navy/30 w-5 text-center shrink-0" x-text="idx + 1"></span>

                                <select :name="'steps[' + idx + '][role]'" x-model="step.role"
                                        class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                                    <option value="analyst">Analista</option>
                                    <option value="strategist">Estratega</option>
                                    <option value="creative">Creativo</option>
                                    <option value="auditor">Auditor</option>
                                    <option value="custom">Personalizado</option>
                                </select>

                                <select :name="'steps[' + idx + '][agent_id]'" x-model="step.agent_id"
                                        class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition">
                                    <option value="">Agente del sistema</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" @click="moveUp(idx)"
                                            class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-navy/30 hover:text-navy/60 hover:border-navy/30 transition"
                                            :disabled="idx === 0">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="moveDown(idx)"
                                            class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-navy/30 hover:text-navy/60 hover:border-navy/30 transition"
                                            :disabled="idx === steps.length - 1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="removeStep(idx)"
                                            class="w-7 h-7 rounded-lg border border-navy/10 flex items-center justify-center text-red-400 hover:text-red-600 hover:border-red-300 transition">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addStep()"
                            class="mt-3 text-sm text-navy/50 hover:text-navy underline underline-offset-4 decoration-navy/20 transition">
                        Agregar paso
                    </button>

                    @error('steps')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-sm text-navy/70">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                               class="rounded border-navy/20 text-copper focus:ring-copper/30">
                        Establecer como secuencia por defecto
                    </label>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                        Crear secuencia
                    </button>
                </div>
            </form>
        </div>
    </section>

</x-layouts.dashboard>
