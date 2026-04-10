<x-layouts.app title="Campaña">
    {{-- ═══════════════════════ HEADER ═══════════════════════ --}}
    <section class="relative mb-10 pb-8 border-b-2 border-navy">
        <svg class="absolute -top-2 right-4 w-14 h-14 text-copper animate-[spin_22s_linear_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>

        <a href="{{ route('campaigns.index') }}" class="inline-flex items-center gap-1 text-xs uppercase tracking-widest text-navy/50 hover:text-navy transition mb-5">
            &larr; Todas las campañas
        </a>

        <div class="flex items-end justify-between gap-6 flex-wrap">
            <div class="flex-1 min-w-0">
                <p class="text-xs uppercase tracking-[0.3em] text-copper font-bold mb-2">
                    / campaña #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
                </p>
                <h1 class="font-[Fredoka] font-bold leading-[0.9] tracking-tight text-[clamp(2rem,6vw,4.5rem)] mb-3">
                    {{ $campaign->strategy['campaign_name'] ?? 'Sin título' }}
                </h1>
                <p class="text-navy/55 max-w-2xl">{{ $campaign->objective }}</p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
                @if ($campaign->quality_score)
                    <div class="relative bg-white rounded-2xl border-2 border-navy px-5 py-3 shadow-[4px_4px_0_0_#1a1a2e] text-center">
                        <p class="text-4xl font-[Fredoka] font-bold leading-none
                            {{ $campaign->quality_score >= 80 ? 'text-emerald-600' : ($campaign->quality_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $campaign->quality_score }}
                        </p>
                        <p class="text-[9px] uppercase tracking-widest text-navy/50 mt-1">/ score</p>
                    </div>
                @endif
                <span class="inline-flex items-center gap-2 text-[10px] uppercase tracking-widest font-bold px-3 py-2 rounded-full border-2 border-navy
                    {{ $campaign->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                    {{ $campaign->status === 'processing' ? 'bg-amber-100 text-amber-800' : '' }}
                    {{ $campaign->status === 'pending' ? 'bg-navy/10 text-navy/60' : '' }}
                    {{ $campaign->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full
                        {{ $campaign->status === 'completed' ? 'bg-emerald-500' : '' }}
                        {{ $campaign->status === 'processing' ? 'bg-amber-500 animate-pulse' : '' }}
                        {{ $campaign->status === 'pending' ? 'bg-navy/40 animate-pulse' : '' }}
                        {{ $campaign->status === 'failed' ? 'bg-red-500' : '' }}"></span>
                    {{ $campaign->status }}
                </span>
            </div>
        </div>
    </section>

    @if ($campaign->isProcessing() || $campaign->isPending())
        <div id="pipeline-status" class="relative bg-white rounded-3xl border-2 border-navy p-8 overflow-hidden shadow-[6px_6px_0_0_#1a1a2e]">
            <div class="absolute inset-0 opacity-[0.04] text-navy">
                <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
            </div>
            <div class="relative">
                <p class="text-xs uppercase tracking-[0.3em] text-copper font-bold mb-6">/ pipeline en ejecución</p>
                <div class="space-y-3">
                    @foreach (['analysis' => 'Analista', 'strategy' => 'Estratega', 'creative' => 'Creativo', 'audit' => 'Auditor'] as $key => $name)
                        @php $done = (bool) $campaign->$key; @endphp
                        <div class="relative flex items-center gap-4 p-4 rounded-2xl border-2 {{ $done ? 'border-emerald-600 bg-emerald-50' : 'border-navy/20 bg-sand-light/40' }} transition-all">
                            <div class="relative w-10 h-10 rounded-xl border-2 {{ $done ? 'border-emerald-600 bg-white' : 'border-navy/20 bg-white' }} flex items-center justify-center shrink-0">
                                @if ($done)
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-navy/30 animate-[spin_3s_linear_infinite]" viewBox="0 0 24 24">
                                        <use href="#roomie-sparkle"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-[Fredoka] font-bold text-base">Agente {{ $name }}</p>
                                <p class="text-xs text-navy/50 mt-0.5">{{ $done ? 'Completado' : 'En espera…' }}</p>
                            </div>
                            <span class="text-xs font-bold text-navy/30">0{{ $loop->iteration }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
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
        {{-- Pipeline step badges --}}
        <div class="grid grid-cols-4 gap-3 mb-10">
            @foreach (['analysis' => 'Analista', 'strategy' => 'Estratega', 'creative' => 'Creativo', 'audit' => 'Auditor'] as $key => $name)
                <div class="relative bg-emerald-50 rounded-2xl border-2 border-emerald-600 p-3 text-center overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.08] text-emerald-700">
                        <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-dots)"/></svg>
                    </div>
                    <div class="relative">
                        <svg class="w-5 h-5 text-emerald-600 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <p class="text-[11px] font-[Fredoka] font-bold text-emerald-800">{{ $name }}</p>
                        <p class="text-[9px] uppercase tracking-widest text-emerald-600/60 mt-0.5">0{{ $loop->iteration }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-6">
            {{-- Left column: Strategy + Audit --}}
            <div class="col-span-3 lg:col-span-1 space-y-6">
                @if ($strategy = $campaign->strategy)
                    <div class="relative bg-white rounded-2xl border-2 border-navy p-6 overflow-hidden shadow-[4px_4px_0_0_#1a1a2e]">
                        <div class="absolute top-0 right-0 w-24 h-24 opacity-[0.06] text-navy">
                            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-plus)"/></svg>
                        </div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-5">
                                <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                <h3 class="font-bold text-xs text-copper uppercase tracking-[0.2em]">Estrategia</h3>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ segmento</p>
                                    <p class="font-[Fredoka] font-bold text-lg">{{ $strategy['target_segment']['name'] ?? '—' }}</p>
                                    <p class="text-sm text-navy/60 mt-1">{{ $strategy['target_segment']['persona'] ?? '' }}</p>
                                </div>

                                <div class="pt-4 border-t border-navy/10">
                                    <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ hotel</p>
                                    <p class="font-[Fredoka] font-bold text-lg">{{ $strategy['recommended_hotel']['name'] ?? '—' }}</p>
                                    <p class="text-sm text-navy/60">{{ $strategy['recommended_hotel']['city'] ?? '' }}</p>
                                    <p class="text-xs text-navy/40 mt-2 italic">{{ $strategy['recommended_hotel']['why'] ?? '' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-navy/10">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ canal</p>
                                        <p class="font-semibold text-sm">{{ $strategy['channel'] ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ timing</p>
                                        <p class="font-semibold text-sm">{{ $strategy['timing']['best_period'] ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-navy/10">
                                    <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ key message</p>
                                    <p class="text-sm font-[Fredoka] font-semibold italic">"{{ $strategy['key_message'] ?? '' }}"</p>
                                </div>

                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ tono</p>
                                    <p class="text-sm">{{ $strategy['tone'] ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($audit = $campaign->audit)
                    <div class="relative bg-navy text-cream rounded-2xl border-2 border-navy p-6 overflow-hidden shadow-[4px_4px_0_0_#c8956c]">
                        <div class="absolute inset-0 opacity-[0.08] text-cream">
                            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-diag)"/></svg>
                        </div>
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-5">
                                <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                <h3 class="font-bold text-xs text-copper uppercase tracking-[0.2em]">Auditoría</h3>
                            </div>

                            <div class="space-y-4">
                                <p class="text-sm leading-relaxed">{{ $audit['summary'] ?? '' }}</p>

                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-copper mb-2">/ coherencia</p>
                                    @foreach (($audit['coherence_check'] ?? []) as $check => $passed)
                                        <div class="flex items-center gap-2 text-sm py-0.5">
                                            @if ($passed)
                                                <span class="text-emerald-400 font-bold">✓</span>
                                            @else
                                                <span class="text-red-400 font-bold">✗</span>
                                            @endif
                                            <span class="text-cream/70">{{ str_replace('_', ' ', $check) }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @if (!empty($audit['strengths']))
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-copper mb-1">/ fortalezas</p>
                                        @foreach ($audit['strengths'] as $s)
                                            <p class="text-sm text-cream/70 flex gap-2">
                                                <span class="text-emerald-400">+</span>
                                                {{ $s }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($audit['improvements']))
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-copper mb-1">/ mejoras</p>
                                        @foreach ($audit['improvements'] as $imp)
                                            <p class="text-sm text-cream/70 flex gap-2">
                                                <span class="text-amber-400">−</span>
                                                {{ $imp }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="pt-3 border-t border-cream/20">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-widest font-bold px-3 py-1.5 rounded-full
                                        {{ ($audit['final_verdict'] ?? '') === 'aprobada' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ ($audit['final_verdict'] ?? '') === 'aprobada' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                        {{ $audit['final_verdict'] ?? '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right column: Email preview --}}
            <div class="col-span-3 lg:col-span-2">
                @if ($creative = $campaign->creative)
                    <div class="relative bg-white rounded-2xl border-2 border-navy overflow-hidden shadow-[6px_6px_0_0_#1a1a2e]">
                        {{-- Email header bar --}}
                        <div class="px-6 py-4 border-b-2 border-navy bg-sand-light flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-400 border border-navy/20"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-navy/20"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 border border-navy/20"></span>
                            </div>
                            <span class="text-[10px] uppercase tracking-widest text-navy/50 ml-3">/ email preview</span>
                        </div>

                        <div class="px-6 py-4 border-b border-sand">
                            <p class="text-sm mb-1"><span class="text-[10px] uppercase tracking-widest text-navy/40">Asunto &nbsp;</span> <strong class="font-[Fredoka]">{{ $creative['subject_line'] ?? '' }}</strong></p>
                            <p class="text-sm text-navy/50">{{ $creative['preview_text'] ?? '' }}</p>
                        </div>

                        <div class="p-6">
                            <div class="rounded-2xl border-2 border-navy overflow-hidden">
                                <div class="relative bg-navy text-cream px-6 py-6 overflow-hidden">
                                    <div class="absolute inset-0 opacity-[0.08] text-cream">
                                        <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
                                    </div>
                                    <div class="relative">
                                        <p class="text-[10px] tracking-[0.25em] uppercase text-copper mb-2 flex items-center gap-2">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                            {{ $strategy['recommended_hotel']['name'] ?? 'Eurostars' }}
                                        </p>
                                        <h2 class="text-2xl font-[Fredoka] font-bold leading-tight">{{ $creative['headline'] ?? '' }}</h2>
                                    </div>
                                </div>
                                <div class="px-6 py-5 text-sm leading-relaxed bg-cream">
                                    {!! $creative['body_html'] ?? '' !!}
                                </div>
                                <div class="px-6 pb-6 pt-2 text-center bg-cream">
                                    <span class="inline-flex items-center gap-2 bg-copper text-navy px-8 py-3 rounded-xl font-[Fredoka] font-bold text-sm border-2 border-navy shadow-[3px_3px_0_0_#1a1a2e]">
                                        {{ $creative['cta_text'] ?? 'Reservar ahora' }}
                                        <svg class="w-3 h-3" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if (!empty($creative['alt_formats']))
                            <div class="px-6 pb-6 border-t-2 border-navy pt-5">
                                <p class="text-[10px] uppercase tracking-[0.2em] text-copper font-bold mb-4 flex items-center gap-2">
                                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                    Formatos alternativos
                                </p>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach ($creative['alt_formats'] as $format => $text)
                                        <div class="bg-sand-light rounded-xl border-2 border-navy/15 p-3">
                                            <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ {{ str_replace('_', ' ', $format) }}</p>
                                            <p class="text-sm">{{ $text }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!empty($creative['visual_direction']))
                            <div class="px-6 pb-6">
                                <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-1">/ dirección visual</p>
                                <p class="text-sm text-navy/60 italic">{{ $creative['visual_direction'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Analysis section --}}
        @if ($analysis = $campaign->analysis)
            <div class="mt-10 relative bg-white rounded-2xl border-2 border-navy p-6 overflow-hidden shadow-[6px_6px_0_0_#1a1a2e]">
                <div class="absolute inset-0 opacity-[0.04] text-navy">
                    <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-grid)"/></svg>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        <h3 class="font-bold text-xs text-copper uppercase tracking-[0.2em]">Análisis de datos</h3>
                    </div>

                    @if (!empty($analysis['segments']))
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            @foreach ($analysis['segments'] as $segment)
                                @php $isFocus = ($segment['name'] ?? '') === ($analysis['recommended_focus_segment'] ?? ''); @endphp
                                <div class="relative rounded-2xl border-2 p-4 overflow-hidden
                                    {{ $isFocus ? 'border-copper bg-copper/5 shadow-[3px_3px_0_0_#c8956c]' : 'border-navy/20 bg-sand-light/40' }}">
                                    <div class="flex items-start justify-between">
                                        <p class="font-[Fredoka] font-bold text-sm">{{ $segment['name'] ?? '' }}</p>
                                        @if ($isFocus)
                                            <span class="inline-flex items-center gap-1 text-[9px] uppercase tracking-widest bg-navy text-copper px-2 py-0.5 rounded-full font-bold">
                                                <svg class="w-2 h-2" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                                Foco
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-navy/50 mt-1 leading-relaxed">{{ $segment['description'] ?? '' }}</p>
                                    <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-navy/10 text-center">
                                        <div>
                                            <p class="text-base font-[Fredoka] font-bold">{{ $segment['size'] ?? '—' }}</p>
                                            <p class="text-[9px] uppercase tracking-widest text-navy/40">clientes</p>
                                        </div>
                                        <div>
                                            <p class="text-base font-[Fredoka] font-bold">{{ isset($segment['avg_adr']) ? number_format($segment['avg_adr'], 0) . '€' : '—' }}</p>
                                            <p class="text-[9px] uppercase tracking-widest text-navy/40">ADR</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold truncate">{{ implode(', ', $segment['preferred_destinations'] ?? []) }}</p>
                                            <p class="text-[9px] uppercase tracking-widest text-navy/40">destinos</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($analysis['market_insights']))
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-navy/40 mb-2">/ market insights</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($analysis['market_insights'] as $insight)
                                    <span class="inline-flex items-center gap-1.5 text-xs bg-sand-light border-2 border-navy/20 px-3 py-1.5 rounded-xl">
                                        <svg class="w-2.5 h-2.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                                        {{ $insight }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    @if ($campaign->isFailed())
        <div class="relative bg-red-50 border-2 border-red-600 rounded-2xl p-8 text-center shadow-[6px_6px_0_0_#dc2626] overflow-hidden">
            <div class="absolute inset-0 opacity-[0.05] text-red-600">
                <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-diag)"/></svg>
            </div>
            <div class="relative">
                <p class="text-2xl font-[Fredoka] font-bold text-red-700">El pipeline ha fallado</p>
                <p class="text-sm text-red-500 mt-1">Revisa los logs o intenta crear una nueva campaña.</p>
            </div>
        </div>
    @endif
</x-layouts.app>
