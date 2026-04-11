<x-layouts.app title="Campaña">
    @php
        $statusLabels = [
            'completed' => 'Completada',
            'processing' => 'En curso',
            'pending' => 'En cola',
            'failed' => 'Fallida',
        ];
        $providerLabel = $campaign->api_provider
            ? \App\Services\LLM\LlmClientFactory::label($campaign->api_provider)
            : null;
        if ($providerLabel && $campaign->api_provider === 'custom' && $campaign->api_model) {
            $providerLabel .= ' · '.$campaign->api_model;
        }
        $intensityLabels = [
            'aggressiveness' => ['Informativa', 'Invitación', 'Equilibrada', 'Persuasiva', 'Insistente', 'Agresiva'],
            'manipulation' => ['Neutral', 'Sutil', 'Con urgencia', 'Con FOMO', 'Con presión', 'Dark patterns'],
        ];
        $agg = $campaign->aggressiveness ?? 2;
        $man = $campaign->manipulation ?? 2;
    @endphp

    <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
        ← Campañas
    </a>

    <header class="pt-3 sm:pt-4 pb-8 sm:pb-9 mb-10 sm:mb-12 border-b border-navy/15">
        <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-3 flex flex-wrap gap-x-2 gap-y-1">
            <span>#{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}</span>
            @if ($campaign->created_at)
                <span>· {{ $campaign->created_at->translatedFormat('d M Y') }}</span>
            @endif
            @if ($providerLabel)
                <span>· {{ $providerLabel }}</span>
            @endif
        </p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl md:text-5xl leading-[1.05] tracking-tight mb-3 sm:mb-4 max-w-3xl">
            {{ $campaign->name ?? $campaign->strategy['campaign_name'] ?? 'Sin título' }}
        </h1>
        <p class="text-navy/60 leading-relaxed max-w-2xl text-[15px] sm:text-base">{{ $campaign->objective }}</p>

        <div class="flex items-center gap-5 sm:gap-7 mt-6 sm:mt-7 flex-wrap">
            @if ($campaign->quality_score)
                <div class="flex items-baseline gap-2">
                    <span class="font-[Fredoka] font-semibold text-2xl
                        {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                        {{ $campaign->quality_score }}<span class="text-navy/30 text-sm font-normal">/100</span>
                    </span>
                    <span class="text-[11px] text-navy/40 uppercase tracking-wider">score</span>
                </div>
            @endif
            <span class="text-xs
                {{ $campaign->status === 'completed' ? 'text-emerald-700' : '' }}
                {{ $campaign->status === 'processing' ? 'text-amber-700' : '' }}
                {{ $campaign->status === 'pending' ? 'text-navy/55' : '' }}
                {{ $campaign->status === 'failed' ? 'text-red-700' : '' }}">
                {{ $statusLabels[$campaign->status] ?? $campaign->status }}
            </span>
            <span class="text-xs text-navy/50">
                Agresividad <span class="font-mono text-navy">{{ $agg }}/5</span> · Manipulación <span class="font-mono text-navy">{{ $man }}/5</span>
            </span>
        </div>
    </header>

    @if ($campaign->isProcessing() || $campaign->isPending())
        <div id="pipeline-container" class="max-w-2xl">

            {{-- Header --}}
            <div class="mb-8 sm:mb-10">
                <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-3 flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-copper opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-copper"></span>
                    </span>
                    Pipeline en ejecución
                </p>
                <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-[1.05] tracking-tight mb-2">
                    Los agentes están trabajando…
                </h2>
                <p class="text-sm text-navy/55 leading-relaxed">
                    Puedes quedarte a ver el proceso o volver al <a href="{{ route('campaigns.index') }}" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">listado</a>. Suele tardar menos de un minuto.
                </p>
            </div>

            {{-- Progress bar + timer --}}
            <div class="mb-8">
                <div class="flex items-baseline justify-between mb-2.5 gap-3">
                    <p class="text-xs text-navy/55 font-mono truncate">
                        <span id="pipeline-percent" class="text-navy font-medium">0</span>% · <span id="pipeline-stage">Inicializando</span>
                    </p>
                    <p class="text-xs text-navy/55 font-mono tabular-nums shrink-0">
                        <span id="pipeline-timer">00:00</span>
                    </p>
                </div>
                <div class="h-1.5 bg-navy/10 rounded-full overflow-hidden">
                    <div id="pipeline-bar" class="h-full bg-copper rounded-full transition-all duration-700 ease-out" style="width: 0%"></div>
                </div>
            </div>

            {{-- Live activity ticker --}}
            <div class="mb-10 py-3.5 px-4 bg-sand-light/60 border-l-2 border-copper rounded-r-xl flex items-center gap-3 min-w-0">
                <svg class="w-3 h-3 text-copper shrink-0" viewBox="0 0 24 24" style="animation: spin 4s linear infinite;" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                <p id="pipeline-activity" class="text-sm text-navy/75 font-mono transition-opacity duration-200 truncate min-w-0">
                    Despertando agentes…
                </p>
            </div>

            {{-- Step list --}}
            <ol>
                @foreach ([
                    ['analysis', 'Analista', 'Lee la base de clientes y los datos de reservas'],
                    ['strategy', 'Estratega', 'Define hotel, canal, timing y mensaje clave'],
                    ['creative', 'Creativo', 'Escribe el asunto, el cuerpo y el CTA'],
                    ['audit', 'Auditor', 'Cruza coherencia y devuelve un score de calidad'],
                ] as $i => [$key, $name, $desc])
                    <li class="pipeline-step py-5 sm:py-6 border-t border-navy/10 last:border-b opacity-40 transition-opacity duration-500" data-step="{{ $key }}">
                        <div class="flex items-start gap-4">
                            {{-- Icon slot --}}
                            <div class="w-6 h-6 flex items-center justify-center shrink-0 mt-1">
                                <span class="step-icon-pending block w-1.5 h-1.5 rounded-full bg-navy/30"></span>
                                <svg class="step-icon-active hidden w-5 h-5 text-copper" viewBox="0 0 24 24" style="animation: spin 3s linear infinite;" aria-hidden="true"><use href="#roomie-sparkle"/></svg>
                                <svg class="step-icon-done hidden w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-3 mb-1">
                                    <p class="font-[Fredoka] font-semibold">
                                        <span class="text-navy/40 font-mono text-xs mr-1.5">0{{ $i + 1 }}</span>
                                        {{ $name }}
                                    </p>
                                    <span class="step-state-pending text-[10px] text-navy/40 uppercase tracking-wider font-mono shrink-0">En espera</span>
                                    <span class="step-state-active hidden text-[10px] text-copper uppercase tracking-wider font-mono shrink-0">Trabajando…</span>
                                    <span class="step-state-done hidden text-[10px] text-emerald-700 uppercase tracking-wider font-mono shrink-0">Listo</span>
                                </div>
                                <p class="step-message text-sm text-navy/55 leading-relaxed">{{ $desc }}</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        @push('scripts')
        <script>
            (function () {
                const steps = [
                    { key: 'analysis', stage: 'Analizando los datos' },
                    { key: 'strategy', stage: 'Definiendo la estrategia' },
                    { key: 'creative', stage: 'Redactando el email' },
                    { key: 'audit', stage: 'Auditando la coherencia' },
                ];

                const activities = {
                    analysis: [
                        'Leyendo la base de clientes…',
                        'Detectando patrones de reserva…',
                        'Segmentando por comportamiento y ADR…',
                        'Cruzando con el catálogo de hoteles…',
                        'Priorizando el segmento con mejor encaje…',
                    ],
                    strategy: [
                        'Eligiendo el hotel más afín al segmento…',
                        'Decidiendo canal y timing óptimos…',
                        'Afinando el mensaje clave…',
                        'Ajustando el tono comunicativo…',
                    ],
                    creative: [
                        'Escribiendo el asunto del email…',
                        'Redactando el cuerpo con estilo editorial…',
                        'Preparando el botón CTA…',
                        'Generando push, SMS y caption social…',
                        'Sugiriendo la dirección visual…',
                    ],
                    audit: [
                        'Revisando coherencia entre fases…',
                        'Verificando que el hotel encaje con el segmento…',
                        'Evaluando el timing propuesto…',
                        'Calculando el score de calidad…',
                        'Escribiendo el veredicto final…',
                    ],
                };

                const state = { analysis: false, strategy: false, creative: false, audit: false };
                const startTime = Date.now();
                let activityIdx = 0;
                let failed = false;

                const timerEl = document.getElementById('pipeline-timer');
                const barEl = document.getElementById('pipeline-bar');
                const percentEl = document.getElementById('pipeline-percent');
                const stageEl = document.getElementById('pipeline-stage');
                const activityEl = document.getElementById('pipeline-activity');

                function currentStepKey() {
                    const next = steps.find(s => !state[s.key]);
                    return next ? next.key : 'audit';
                }

                function setStepVisualState(key, visual) {
                    const li = document.querySelector('[data-step="' + key + '"]');
                    if (!li) return;
                    li.classList.toggle('opacity-40', visual === 'pending');
                    ['pending', 'active', 'done'].forEach(s => {
                        const icon = li.querySelector('.step-icon-' + s);
                        const label = li.querySelector('.step-state-' + s);
                        if (icon) icon.classList.toggle('hidden', s !== visual);
                        if (label) label.classList.toggle('hidden', s !== visual);
                    });
                }

                function render() {
                    // Timer
                    const elapsed = Math.floor((Date.now() - startTime) / 1000);
                    timerEl.textContent =
                        String(Math.floor(elapsed / 60)).padStart(2, '0') + ':' +
                        String(elapsed % 60).padStart(2, '0');

                    // Progress
                    const doneCount = steps.filter(s => state[s.key]).length;
                    const pct = failed
                        ? doneCount * 25
                        : Math.min(doneCount * 25 + 12, 98);
                    barEl.style.width = pct + '%';
                    percentEl.textContent = Math.round(pct);

                    // Stage label
                    const activeKey = currentStepKey();
                    const activeStep = steps.find(s => s.key === activeKey);
                    stageEl.textContent = activeStep ? activeStep.stage : 'Finalizando';

                    // Step icons / labels
                    steps.forEach(step => {
                        if (state[step.key]) setStepVisualState(step.key, 'done');
                        else if (step.key === activeKey && !failed) setStepVisualState(step.key, 'active');
                        else setStepVisualState(step.key, 'pending');
                    });
                }

                function cycleActivity() {
                    if (failed) {
                        activityEl.textContent = 'Algo ha fallado. Recargando para ver el error…';
                        return;
                    }
                    const activeKey = currentStepKey();
                    const list = activities[activeKey] || [];
                    if (!list.length) return;
                    const msg = list[activityIdx % list.length];
                    activityEl.style.opacity = '0';
                    setTimeout(() => {
                        activityEl.textContent = msg;
                        activityEl.style.opacity = '1';
                    }, 220);
                    activityIdx++;
                }

                function applyPreview(key, text) {
                    if (!text) return;
                    const li = document.querySelector('[data-step="' + key + '"]');
                    if (!li) return;
                    const msg = li.querySelector('.step-message');
                    if (msg) msg.textContent = text;
                }

                function setPreviews(data) {
                    if (data.analysis_preview) {
                        const n = data.analysis_preview.segments_count || 0;
                        const focus = data.analysis_preview.focus_segment;
                        applyPreview('analysis', n + ' segmentos identificados' + (focus ? ' · foco: ' + focus : ''));
                    }
                    if (data.strategy_preview) {
                        const parts = [];
                        if (data.strategy_preview.hotel_name) parts.push(data.strategy_preview.hotel_name);
                        if (data.strategy_preview.channel) parts.push(data.strategy_preview.channel);
                        if (parts.length) applyPreview('strategy', parts.join(' · '));
                    }
                    if (data.creative_preview && data.creative_preview.subject) {
                        applyPreview('creative', '"' + data.creative_preview.subject + '"');
                    }
                    if (data.audit_preview) {
                        const verdict = data.audit_preview.verdict;
                        const score = data.quality_score;
                        if (score != null) applyPreview('audit', score + '/100' + (verdict ? ' · ' + verdict : ''));
                    }
                }

                render();
                cycleActivity();
                setInterval(render, 1000);
                setInterval(cycleActivity, 2800);

                (function poll() {
                    setTimeout(async () => {
                        try {
                            const res = await fetch('{{ route("campaigns.status", $campaign) }}');
                            const data = await res.json();

                            state.analysis = data.has_analysis;
                            state.strategy = data.has_strategy;
                            state.creative = data.has_creative;
                            state.audit = data.has_audit;
                            setPreviews(data);
                            render();

                            if (data.status === 'completed') {
                                barEl.style.width = '100%';
                                percentEl.textContent = '100';
                                stageEl.textContent = 'Completado';
                                setTimeout(() => window.location.reload(), 900);
                                return;
                            }
                            if (data.status === 'failed') {
                                failed = true;
                                render();
                                cycleActivity();
                                setTimeout(() => window.location.reload(), 1500);
                                return;
                            }
                            poll();
                        } catch (e) {
                            poll();
                        }
                    }, 2500);
                })();
            })();
        </script>
        @endpush
    @endif

    @if ($campaign->isComplete())
        <div class="grid grid-cols-12 gap-x-8 lg:gap-x-10 gap-y-10 sm:gap-y-12">
            {{-- Sidebar: estrategia + auditoría --}}
            <aside class="col-span-12 lg:col-span-4 space-y-10 sm:space-y-12">
                @if ($strategy = $campaign->strategy)
                    <section>
                        <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">Estrategia</p>

                        <dl class="space-y-6">
                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Segmento</dt>
                                <dd class="font-[Fredoka] font-semibold">{{ $strategy['target_segment']['name'] ?? '—' }}</dd>
                                <dd class="text-sm text-navy/60 mt-1 leading-relaxed">{{ $strategy['target_segment']['persona'] ?? '' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Hotel</dt>
                                <dd class="font-[Fredoka] font-semibold">{{ $strategy['recommended_hotel']['name'] ?? '—' }}</dd>
                                <dd class="text-sm text-navy/60">{{ $strategy['recommended_hotel']['city'] ?? '' }}</dd>
                                <dd class="text-xs text-navy/45 italic mt-2 leading-relaxed">{{ $strategy['recommended_hotel']['why'] ?? '' }}</dd>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-navy/45 mb-1">Canal</dt>
                                    <dd class="text-sm">{{ $strategy['channel'] ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-navy/45 mb-1">Timing</dt>
                                    <dd class="text-sm">{{ $strategy['timing']['best_period'] ?? '—' }}</dd>
                                </div>
                            </div>

                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Mensaje clave</dt>
                                <dd class="text-sm italic font-[Fredoka] leading-relaxed">"{{ $strategy['key_message'] ?? '' }}"</dd>
                            </div>

                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Tono</dt>
                                <dd class="text-sm">{{ $strategy['tone'] ?? '' }}</dd>
                            </div>
                        </dl>
                    </section>
                @endif

                @if ($audit = $campaign->audit)
                    <section class="border-t border-navy/10 pt-10">
                        <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">Auditoría</p>
                        <p class="text-sm text-navy/70 leading-relaxed mb-6">{{ $audit['summary'] ?? '' }}</p>

                        @if (!empty($audit['coherence_check']))
                            <div class="mb-6 space-y-1.5">
                                @foreach ($audit['coherence_check'] as $check => $passed)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="{{ $passed ? 'text-emerald-600' : 'text-red-600' }}">{{ $passed ? '✓' : '✕' }}</span>
                                        <span class="text-navy/55">{{ str_replace('_', ' ', $check) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($audit['strengths']))
                            <div class="mb-5">
                                <p class="text-xs text-navy/45 mb-1.5">Fortalezas</p>
                                @foreach ($audit['strengths'] as $s)
                                    <p class="text-xs text-navy/65 leading-relaxed">+ {{ $s }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($audit['improvements']))
                            <div>
                                <p class="text-xs text-navy/45 mb-1.5">Mejoras</p>
                                @foreach ($audit['improvements'] as $imp)
                                    <p class="text-xs text-navy/65 leading-relaxed">− {{ $imp }}</p>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </aside>

            {{-- Main: email preview --}}
            <main class="col-span-12 lg:col-span-8">
                @if ($creative = $campaign->creative)
                    <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">Email</p>

                    <div class="bg-white border border-navy/15 rounded-2xl overflow-hidden">
                        <div class="px-5 sm:px-7 py-4 sm:py-5 border-b border-navy/10">
                            <p class="text-xs text-navy/45 mb-1">Asunto</p>
                            <p class="font-[Fredoka] font-semibold text-base sm:text-lg leading-tight">{{ $creative['subject_line'] ?? '' }}</p>
                            <p class="text-sm text-navy/55 mt-2">{{ $creative['preview_text'] ?? '' }}</p>
                        </div>

                        <div class="bg-navy text-cream px-5 sm:px-8 py-7 sm:py-8">
                            <p class="text-[11px] tracking-[0.18em] uppercase text-copper mb-3">{{ $strategy['recommended_hotel']['name'] ?? 'Eurostars' }}</p>
                            <h2 class="font-[Fredoka] font-semibold text-xl sm:text-2xl md:text-3xl leading-tight">{{ $creative['headline'] ?? '' }}</h2>
                        </div>

                        <div class="px-5 sm:px-8 py-6 sm:py-7 text-sm leading-relaxed text-navy/80 break-words">
                            {!! $creative['body_html'] ?? '' !!}
                        </div>

                        <div class="px-5 sm:px-8 pb-7 sm:pb-8 text-center">
                            <span class="inline-block bg-copper text-navy px-7 py-2.5 rounded-full text-sm font-medium">
                                {{ $creative['cta_text'] ?? 'Reservar ahora' }}
                            </span>
                        </div>
                    </div>

                    @if (!empty($creative['alt_formats']))
                        <section class="mt-12">
                            <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">Otras versiones</p>
                            <div class="space-y-5">
                                @foreach ($creative['alt_formats'] as $format => $text)
                                    <div>
                                        <p class="text-xs text-navy/45 mb-1">{{ str_replace('_', ' ', $format) }}</p>
                                        <p class="text-sm text-navy/75 leading-relaxed">{{ $text }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if (!empty($creative['visual_direction']))
                        <section class="mt-9">
                            <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-2">Dirección visual</p>
                            <p class="text-sm text-navy/65 italic leading-relaxed">{{ $creative['visual_direction'] }}</p>
                        </section>
                    @endif
                @endif
            </main>
        </div>

        {{-- Análisis --}}
        @if ($analysis = $campaign->analysis)
            <section class="mt-20 pt-12 border-t border-navy/15">
                <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-7">Análisis</p>

                @if (!empty($analysis['segments']))
                    <div class="grid md:grid-cols-2 gap-x-8 lg:gap-x-10 gap-y-8 sm:gap-y-9 mb-10 sm:mb-12">
                        @foreach ($analysis['segments'] as $segment)
                            @php $isFocus = ($segment['name'] ?? '') === ($analysis['recommended_focus_segment'] ?? ''); @endphp
                            <div>
                                <div class="flex items-baseline gap-3 mb-1.5">
                                    <h4 class="font-[Fredoka] font-semibold text-lg">{{ $segment['name'] ?? '' }}</h4>
                                    @if ($isFocus)
                                        <span class="text-[10px] uppercase tracking-wider text-copper font-medium">foco</span>
                                    @endif
                                </div>
                                <p class="text-sm text-navy/60 leading-relaxed mb-4">{{ $segment['description'] ?? '' }}</p>
                                <dl class="grid grid-cols-3 gap-3 text-xs">
                                    <div class="min-w-0">
                                        <dt class="text-navy/45">Clientes</dt>
                                        <dd class="font-[Fredoka] font-semibold text-base mt-0.5">{{ $segment['size'] ?? '—' }}</dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-navy/45">ADR</dt>
                                        <dd class="font-[Fredoka] font-semibold text-base mt-0.5">{{ isset($segment['avg_adr']) ? number_format($segment['avg_adr'], 0) . '€' : '—' }}</dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-navy/45">Destinos</dt>
                                        <dd class="text-xs mt-0.5 truncate">{{ implode(', ', $segment['preferred_destinations'] ?? []) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!empty($analysis['market_insights']))
                    <div>
                        <p class="text-xs text-navy/45 mb-3">Market insights</p>
                        <ul class="space-y-2">
                            @foreach ($analysis['market_insights'] as $insight)
                                <li class="flex items-start gap-2.5 text-sm text-navy/70 leading-relaxed">
                                    <span class="text-copper mt-1.5 shrink-0">·</span>
                                    {{ $insight }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endif
    @endif

    @if ($campaign->isFailed())
        <div class="border-l-2 border-red-500 pl-4 py-2">
            <p class="font-medium text-red-700">El pipeline ha fallado</p>
            <p class="text-sm text-red-500/80 mt-1">Revisa los logs o crea una nueva campaña.</p>
        </div>
    @endif
</x-layouts.app>
