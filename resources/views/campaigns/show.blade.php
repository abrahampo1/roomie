<x-layouts.app title="Campaña">
    <div class="mb-8">
        <a href="{{ route('campaigns.index') }}" class="text-sm text-navy/40 hover:text-navy transition">&larr; Todas las campañas</a>

        <div class="flex items-start justify-between mt-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">
                    {{ $campaign->strategy['campaign_name'] ?? 'Campaña #' . $campaign->id }}
                </h1>
                <p class="text-navy/50 mt-1">{{ $campaign->objective }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if ($campaign->quality_score)
                    <div class="text-center">
                        <p class="text-3xl font-bold {{ $campaign->quality_score >= 80 ? 'text-emerald-600' : ($campaign->quality_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $campaign->quality_score }}
                        </p>
                        <p class="text-xs text-navy/40">calidad</p>
                    </div>
                @endif
                <span class="text-xs px-3 py-1.5 rounded-full font-medium
                    {{ $campaign->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : '' }}
                    {{ $campaign->status === 'processing' ? 'bg-amber-50 text-amber-700 animate-pulse' : '' }}
                    {{ $campaign->status === 'pending' ? 'bg-navy/5 text-navy/50 animate-pulse' : '' }}
                    {{ $campaign->status === 'failed' ? 'bg-red-50 text-red-700' : '' }}">
                    {{ $campaign->status }}
                </span>
            </div>
        </div>
    </div>

    @if ($campaign->isProcessing() || $campaign->isPending())
        <div id="pipeline-status" class="space-y-4">
            @foreach (['analysis' => 'Analista', 'strategy' => 'Estratega', 'creative' => 'Creativo', 'audit' => 'Auditor'] as $key => $name)
                <div class="bg-white rounded-xl border border-sand p-5 flex items-center gap-4 {{ $campaign->$key ? '' : 'opacity-40' }}">
                    <div class="w-8 h-8 rounded-lg {{ $campaign->$key ? 'bg-emerald-100' : 'bg-navy/5' }} flex items-center justify-center">
                        @if ($campaign->$key)
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        @else
                            <div class="w-3 h-3 rounded-full bg-navy/20 animate-pulse"></div>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-sm">Agente {{ $name }}</p>
                        <p class="text-xs text-navy/40">{{ $campaign->$key ? 'Completado' : 'En espera...' }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @push('scripts')
        <script>
            (function poll() {
                setTimeout(async () => {
                    try {
                        const res = await fetch('{{ route("campaigns.status", $campaign) }}');
                        const data = await res.json();
                        if (data.status === 'completed' || data.status === 'failed') {
                            window.location.reload();
                        } else {
                            poll();
                        }
                    } catch { poll(); }
                }, 3000);
            })();
        </script>
        @endpush
    @endif

    @if ($campaign->isComplete())
        {{-- Pipeline steps --}}
        <div class="grid grid-cols-4 gap-3 mb-10">
            @foreach (['analysis' => 'Analista', 'strategy' => 'Estratega', 'creative' => 'Creativo', 'audit' => 'Auditor'] as $key => $name)
                <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-3 text-center">
                    <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <p class="text-xs font-semibold text-emerald-700">{{ $name }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-6">
            {{-- Left column: Strategy --}}
            <div class="col-span-1 space-y-6">
                {{-- Target --}}
                @if ($strategy = $campaign->strategy)
                    <div class="bg-white rounded-2xl border border-sand p-6">
                        <h3 class="font-semibold text-sm text-navy/40 uppercase tracking-wider mb-4">Estrategia</h3>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-navy/40">Segmento objetivo</p>
                                <p class="font-semibold">{{ $strategy['target_segment']['name'] ?? '—' }}</p>
                                <p class="text-sm text-navy/60 mt-1">{{ $strategy['target_segment']['persona'] ?? '' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-navy/40">Hotel recomendado</p>
                                <p class="font-semibold">{{ $strategy['recommended_hotel']['name'] ?? '—' }}</p>
                                <p class="text-sm text-navy/60">{{ $strategy['recommended_hotel']['city'] ?? '' }}</p>
                                <p class="text-xs text-navy/40 mt-1">{{ $strategy['recommended_hotel']['why'] ?? '' }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-navy/40">Canal</p>
                                    <p class="font-medium text-sm">{{ $strategy['channel'] ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-navy/40">Timing</p>
                                    <p class="font-medium text-sm">{{ $strategy['timing']['best_period'] ?? '—' }}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-navy/40">Mensaje clave</p>
                                <p class="text-sm font-medium italic">"{{ $strategy['key_message'] ?? '' }}"</p>
                            </div>

                            <div>
                                <p class="text-xs text-navy/40">Tono</p>
                                <p class="text-sm">{{ $strategy['tone'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Audit --}}
                @if ($audit = $campaign->audit)
                    <div class="bg-white rounded-2xl border border-sand p-6">
                        <h3 class="font-semibold text-sm text-navy/40 uppercase tracking-wider mb-4">Auditoría</h3>

                        <div class="space-y-3">
                            <p class="text-sm">{{ $audit['summary'] ?? '' }}</p>

                            <div>
                                <p class="text-xs text-navy/40 mb-2">Checks de coherencia</p>
                                @foreach (($audit['coherence_check'] ?? []) as $check => $passed)
                                    <div class="flex items-center gap-2 text-sm">
                                        @if ($passed)
                                            <span class="text-emerald-500">&#10003;</span>
                                        @else
                                            <span class="text-red-500">&#10007;</span>
                                        @endif
                                        <span class="text-navy/60">{{ str_replace('_', ' ', $check) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            @if (!empty($audit['strengths']))
                                <div>
                                    <p class="text-xs text-navy/40 mb-1">Fortalezas</p>
                                    @foreach ($audit['strengths'] as $s)
                                        <p class="text-sm text-navy/60">+ {{ $s }}</p>
                                    @endforeach
                                </div>
                            @endif

                            @if (!empty($audit['improvements']))
                                <div>
                                    <p class="text-xs text-navy/40 mb-1">Mejoras</p>
                                    @foreach ($audit['improvements'] as $imp)
                                        <p class="text-sm text-navy/60">- {{ $imp }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-2 border-t border-sand">
                                <span class="text-xs px-2 py-1 rounded-full font-medium
                                    {{ ($audit['final_verdict'] ?? '') === 'aprobada' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $audit['final_verdict'] ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right column: Email preview --}}
            <div class="col-span-2">
                @if ($creative = $campaign->creative)
                    <div class="bg-white rounded-2xl border border-sand overflow-hidden">
                        <div class="px-6 py-4 border-b border-sand bg-sand-light/50">
                            <h3 class="font-semibold text-sm text-navy/40 uppercase tracking-wider mb-3">Preview del email</h3>
                            <div class="space-y-1">
                                <p class="text-sm"><span class="text-navy/40">Asunto:</span> <strong>{{ $creative['subject_line'] ?? '' }}</strong></p>
                                <p class="text-sm text-navy/50">{{ $creative['preview_text'] ?? '' }}</p>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="rounded-xl border border-sand overflow-hidden">
                                <div class="bg-navy text-sand-light px-6 py-4">
                                    <p class="text-xs tracking-wider uppercase opacity-60 mb-1">{{ $strategy['recommended_hotel']['name'] ?? 'Eurostars' }}</p>
                                    <h2 class="text-xl font-bold">{{ $creative['headline'] ?? '' }}</h2>
                                </div>
                                <div class="px-6 py-5 text-sm leading-relaxed">
                                    {!! $creative['body_html'] ?? '' !!}
                                </div>
                                <div class="px-6 pb-6 text-center">
                                    <span class="inline-block bg-copper text-white px-8 py-3 rounded-lg font-semibold text-sm">
                                        {{ $creative['cta_text'] ?? 'Reservar ahora' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Alt formats --}}
                        @if (!empty($creative['alt_formats']))
                            <div class="px-6 pb-6 border-t border-sand pt-5">
                                <h4 class="font-semibold text-sm text-navy/40 uppercase tracking-wider mb-4">Formatos alternativos</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach ($creative['alt_formats'] as $format => $text)
                                        <div class="bg-sand-light/50 rounded-lg p-3">
                                            <p class="text-xs text-navy/40 mb-1">{{ str_replace('_', ' ', $format) }}</p>
                                            <p class="text-sm">{{ $text }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($creative['visual_direction']))
                            <div class="px-6 pb-6">
                                <p class="text-xs text-navy/40 mb-1">Dirección visual sugerida</p>
                                <p class="text-sm text-navy/60 italic">{{ $creative['visual_direction'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Analysis section --}}
        @if ($analysis = $campaign->analysis)
            <div class="mt-10 bg-white rounded-2xl border border-sand p-6">
                <h3 class="font-semibold text-sm text-navy/40 uppercase tracking-wider mb-6">Análisis de datos</h3>

                @if (!empty($analysis['segments']))
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        @foreach ($analysis['segments'] as $segment)
                            <div class="rounded-xl border border-sand p-4 {{ ($segment['name'] ?? '') === ($analysis['recommended_focus_segment'] ?? '') ? 'ring-2 ring-navy/20 bg-sand-light/30' : '' }}">
                                <div class="flex items-start justify-between">
                                    <p class="font-semibold text-sm">{{ $segment['name'] ?? '' }}</p>
                                    @if (($segment['name'] ?? '') === ($analysis['recommended_focus_segment'] ?? ''))
                                        <span class="text-[10px] bg-navy text-sand-light px-2 py-0.5 rounded-full">Foco</span>
                                    @endif
                                </div>
                                <p class="text-xs text-navy/50 mt-1">{{ $segment['description'] ?? '' }}</p>
                                <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                                    <div>
                                        <p class="text-sm font-semibold">{{ $segment['size'] ?? '—' }}</p>
                                        <p class="text-[10px] text-navy/40">clientes</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold">{{ isset($segment['avg_adr']) ? number_format($segment['avg_adr'], 0) . '€' : '—' }}</p>
                                        <p class="text-[10px] text-navy/40">ADR medio</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold">{{ implode(', ', $segment['preferred_destinations'] ?? []) }}</p>
                                        <p class="text-[10px] text-navy/40">destinos</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!empty($analysis['market_insights']))
                    <div class="mb-4">
                        <p class="text-xs text-navy/40 mb-2">Market insights</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($analysis['market_insights'] as $insight)
                                <span class="text-xs bg-sand-light border border-sand px-3 py-1.5 rounded-lg">{{ $insight }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

    @if ($campaign->isFailed())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
            <p class="text-red-700 font-medium">El pipeline ha fallado</p>
            <p class="text-sm text-red-500 mt-1">Revisa los logs o intenta crear una nueva campaña.</p>
        </div>
    @endif
</x-layouts.app>
