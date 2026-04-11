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
    @endphp

    <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition">
        ← Campañas
    </a>

    <header class="pt-4 pb-9 mb-12 border-b border-navy/15">
        <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-3">
            #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
            @if ($campaign->created_at)
                · {{ $campaign->created_at->translatedFormat('d M Y') }}
            @endif
            @if ($providerLabel)
                · {{ $providerLabel }}
            @endif
        </p>
        <h1 class="font-[Fredoka] font-semibold text-4xl md:text-5xl leading-[1.05] tracking-tight mb-4 max-w-3xl">
            {{ $campaign->strategy['campaign_name'] ?? 'Sin título' }}
        </h1>
        <p class="text-navy/60 leading-relaxed max-w-2xl">{{ $campaign->objective }}</p>

        <div class="flex items-center gap-7 mt-7">
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
        </div>
    </header>

    @if ($campaign->isProcessing() || $campaign->isPending())
        <div class="max-w-md">
            <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">Pipeline</p>
            <ol class="space-y-1">
                @foreach (['analysis' => 'Analista', 'strategy' => 'Estratega', 'creative' => 'Creativo', 'audit' => 'Auditor'] as $key => $name)
                    @php $done = (bool) $campaign->$key; @endphp
                    <li class="flex items-center gap-3 py-2.5">
                        <span class="w-5 h-5 flex items-center justify-center shrink-0">
                            @if ($done)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-navy/30 animate-pulse"></span>
                            @endif
                        </span>
                        <span class="text-sm {{ $done ? 'text-navy' : 'text-navy/45' }}">{{ $name }}</span>
                        <span class="text-[11px] text-navy/35 ml-auto">{{ $done ? 'Listo' : 'En espera' }}</span>
                    </li>
                @endforeach
            </ol>
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
        <div class="grid grid-cols-12 gap-x-10 gap-y-12">
            {{-- Sidebar: estrategia + auditoría --}}
            <aside class="col-span-12 lg:col-span-4 space-y-12">
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
                        <div class="px-7 py-5 border-b border-navy/10">
                            <p class="text-xs text-navy/45 mb-1">Asunto</p>
                            <p class="font-[Fredoka] font-semibold text-lg leading-tight">{{ $creative['subject_line'] ?? '' }}</p>
                            <p class="text-sm text-navy/55 mt-2">{{ $creative['preview_text'] ?? '' }}</p>
                        </div>

                        <div class="bg-navy text-cream px-8 py-8">
                            <p class="text-[11px] tracking-[0.18em] uppercase text-copper mb-3">{{ $strategy['recommended_hotel']['name'] ?? 'Eurostars' }}</p>
                            <h2 class="font-[Fredoka] font-semibold text-2xl md:text-3xl leading-tight">{{ $creative['headline'] ?? '' }}</h2>
                        </div>

                        <div class="px-8 py-7 text-sm leading-relaxed text-navy/80">
                            {!! $creative['body_html'] ?? '' !!}
                        </div>

                        <div class="px-8 pb-8 text-center">
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
                    <div class="grid md:grid-cols-2 gap-x-10 gap-y-9 mb-12">
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
                                <dl class="flex gap-7 text-xs">
                                    <div>
                                        <dt class="text-navy/45">Clientes</dt>
                                        <dd class="font-[Fredoka] font-semibold text-base mt-0.5">{{ $segment['size'] ?? '—' }}</dd>
                                    </div>
                                    <div>
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
