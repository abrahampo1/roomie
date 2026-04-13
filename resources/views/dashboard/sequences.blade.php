<x-layouts.dashboard title="Secuencias" active="sequences">

    {{-- Header --}}
    <header class="pb-7 sm:pb-8 mb-8">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-2">Pipeline</p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Secuencias</h1>
        <p class="mt-2 text-sm text-navy/55 max-w-xl">Define el orden de ejecución de los agentes. Arrastra los pasos para reordenarlos.</p>
    </header>

    {{-- Default pipeline --}}
    <section class="mb-10">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Pipeline por defecto</p>
        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
            <div class="flex flex-wrap items-center gap-3">
                @php
                    $defaultFlow = [
                        ['label' => 'Analista', 'color' => 'bg-navy/10 text-navy'],
                        ['label' => 'Estratega', 'color' => 'bg-navy/10 text-navy'],
                        ['label' => 'Creativo', 'color' => 'bg-copper/15 text-copper-dark'],
                        ['label' => 'Auditor', 'color' => 'bg-emerald-50 text-emerald-700'],
                    ];
                @endphp
                @foreach ($defaultFlow as $i => $step)
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl {{ $step['color'] }} text-sm font-medium">
                            <span class="font-mono text-[10px] opacity-50">{{ $i + 1 }}</span>
                            {{ $step['label'] }}
                        </span>
                        @if ($i < count($defaultFlow) - 1)
                            <svg class="w-4 h-4 text-navy/20 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-navy/35 mt-3">Cada paso recibe la salida del anterior como contexto.</p>
        </div>
    </section>

    {{-- Create sequence --}}
    <section class="mb-10">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Crear secuencia</p>

        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6"
             x-data="sequenceBuilder({{ Js::from($agents->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])) }})">

            <form method="POST" action="{{ route('dashboard.sequences.store') }}" @submit="syncHidden()">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs text-navy/55 mb-1.5">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Mi secuencia"
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30">
                    </div>
                    <div>
                        <label class="block text-xs text-navy/55 mb-1.5">Descripción</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               placeholder="Pipeline optimizado para..."
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30">
                    </div>
                </div>

                {{-- Drag & drop step builder --}}
                <div class="mb-6">
                    <label class="block text-xs text-navy/55 mb-3">Pasos del pipeline <span class="text-navy/30">(arrastra para reordenar)</span></label>

                    <div class="space-y-2" x-ref="stepList">
                        <template x-for="(step, idx) in steps" :key="step._id">
                            <div class="flex items-center gap-2 bg-navy/[0.02] rounded-xl p-3 transition-all"
                                 :class="{ 'opacity-50 scale-[0.98]': dragging === step._id, 'ring-2 ring-copper/40': dropTarget === idx }"
                                 draggable="true"
                                 @dragstart="onDragStart($event, idx)"
                                 @dragend="onDragEnd()"
                                 @dragover.prevent="onDragOver($event, idx)"
                                 @dragleave="onDragLeave(idx)"
                                 @drop.prevent="onDrop(idx)">

                                {{-- Drag handle --}}
                                <div class="cursor-grab active:cursor-grabbing text-navy/25 hover:text-navy/50 transition shrink-0 touch-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                </div>

                                <span class="font-mono text-[10px] text-navy/25 w-4 text-center shrink-0" x-text="idx + 1"></span>

                                <select x-model="step.role"
                                        class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition select-styled">
                                    <option value="analyst">Analista</option>
                                    <option value="strategist">Estratega</option>
                                    <option value="creative">Creativo</option>
                                    <option value="auditor">Auditor</option>
                                    <option value="custom">Personalizado</option>
                                </select>

                                <template x-if="step.role === 'custom'">
                                    <select x-model="step.agent_id"
                                            class="flex-1 rounded-xl border border-navy/20 bg-white px-3 py-2 text-sm focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition select-styled">
                                        <option value="">Selecciona agente...</option>
                                        <template x-for="a in customAgents" :key="a.id">
                                            <option :value="a.id" x-text="a.name"></option>
                                        </template>
                                    </select>
                                </template>

                                <button type="button" @click="removeStep(idx)" x-show="steps.length > 1"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-navy/25 hover:text-red-600 transition cursor-pointer shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addStep()"
                            class="mt-3 inline-flex items-center gap-1.5 text-xs text-navy/45 hover:text-navy transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Añadir paso
                    </button>

                    @error('steps') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Preview --}}
                <div class="mb-6 p-4 rounded-xl bg-navy/[0.02]">
                    <p class="font-mono text-[10px] text-navy/35 uppercase tracking-wider mb-2">Vista previa</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="(step, idx) in steps" :key="step._id + '-preview'">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium"
                                      :class="step.role === 'creative' ? 'bg-copper/15 text-copper-dark' : (step.role === 'auditor' ? 'bg-emerald-50 text-emerald-700' : 'bg-navy/10 text-navy')">
                                    <span class="font-mono text-[9px] opacity-50" x-text="idx + 1"></span>
                                    <span x-text="{ analyst: 'Analista', strategist: 'Estratega', creative: 'Creativo', auditor: 'Auditor', custom: 'Custom' }[step.role] || step.role"></span>
                                </span>
                                <template x-if="idx < steps.length - 1">
                                    <svg class="w-3 h-3 text-navy/20 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-navy/60">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-navy/20 text-copper focus:ring-copper/30">
                        Secuencia por defecto
                    </label>
                </div>

                {{-- Hidden inputs populated on submit --}}
                <input type="hidden" name="steps_json" x-ref="stepsHidden">

                <div class="mt-5 pt-4 border-t border-navy/10">
                    <button type="submit"
                            class="bg-navy text-cream px-6 py-3 rounded-full text-sm font-medium hover:bg-navy-light transition cursor-pointer">
                        Crear secuencia
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- User sequences --}}
    @if ($sequences->isNotEmpty())
        <section>
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Mis secuencias</p>
            <div class="space-y-4">
                @foreach ($sequences as $seq)
                    <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-[Fredoka] font-semibold text-navy">{{ $seq->name }}</h3>
                                    @if ($seq->is_default)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded-full bg-copper/10 text-copper">por defecto</span>
                                    @endif
                                </div>
                                @if ($seq->description)
                                    <p class="text-xs text-navy/50 mt-1">{{ $seq->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <form method="POST" action="{{ route('dashboard.sequences.destroy', $seq) }}">
                                    @csrf
                                    <button type="submit" onclick="return confirm('¿Eliminar?')"
                                            class="text-[11px] text-navy/40 hover:text-red-700 transition underline underline-offset-4 decoration-navy/20 cursor-pointer">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($seq->steps as $j => $step)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium
                                        {{ ($step['role'] ?? '') === 'creative' ? 'bg-copper/15 text-copper-dark' : (($step['role'] ?? '') === 'auditor' ? 'bg-emerald-50 text-emerald-700' : 'bg-navy/10 text-navy/70') }}">
                                        <span class="font-mono text-[9px] opacity-50">{{ $j + 1 }}</span>
                                        {{ ['analyst' => 'Analista', 'strategist' => 'Estratega', 'creative' => 'Creativo', 'auditor' => 'Auditor', 'custom' => 'Custom'][$step['role'] ?? ''] ?? $step['role'] ?? '?' }}
                                    </span>
                                    @if ($j < count($seq->steps) - 1)
                                        <svg class="w-3 h-3 text-navy/15 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @push('scripts')
    <script>
        function sequenceBuilder(customAgents) {
            let _nextId = 1;
            return {
                customAgents,
                steps: [
                    { _id: _nextId++, role: 'analyst', agent_id: '' },
                    { _id: _nextId++, role: 'strategist', agent_id: '' },
                    { _id: _nextId++, role: 'creative', agent_id: '' },
                    { _id: _nextId++, role: 'auditor', agent_id: '' },
                ],
                dragging: null,
                dragIdx: null,
                dropTarget: null,

                addStep() {
                    this.steps.push({ _id: _nextId++, role: 'analyst', agent_id: '' });
                },
                removeStep(idx) {
                    if (this.steps.length > 1) this.steps.splice(idx, 1);
                },

                onDragStart(e, idx) {
                    this.dragging = this.steps[idx]._id;
                    this.dragIdx = idx;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', idx);
                },
                onDragEnd() {
                    this.dragging = null;
                    this.dragIdx = null;
                    this.dropTarget = null;
                },
                onDragOver(e, idx) {
                    if (this.dragIdx === null || this.dragIdx === idx) return;
                    e.dataTransfer.dropEffect = 'move';
                    this.dropTarget = idx;
                },
                onDragLeave(idx) {
                    if (this.dropTarget === idx) this.dropTarget = null;
                },
                onDrop(toIdx) {
                    const fromIdx = this.dragIdx;
                    if (fromIdx === null || fromIdx === toIdx) return;
                    const item = this.steps.splice(fromIdx, 1)[0];
                    this.steps.splice(toIdx, 0, item);
                    this.dragging = null;
                    this.dragIdx = null;
                    this.dropTarget = null;
                },

                syncHidden() {
                    const clean = this.steps.map(s => ({ role: s.role, agent_id: s.agent_id || null }));
                    this.$refs.stepsHidden.value = JSON.stringify(clean);
                }
            };
        }
    </script>
    @endpush

</x-layouts.dashboard>
