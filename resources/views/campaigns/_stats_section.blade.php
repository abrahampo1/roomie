@php
    $demoMode = ! (bool) config('services.roomie.allow_real_sends', false);
@endphp

<div>
    <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5 flex items-center gap-3">
        Dashboard
        @if ($demoMode)
            <span class="inline-flex items-center gap-1.5 text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full normal-case tracking-normal font-medium">
                <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                Modo demo · engagement simulado
            </span>
        @endif
        <button type="button" wire:click="$refresh" class="ml-auto text-navy/40 hover:text-navy transition underline underline-offset-4 decoration-navy/20 normal-case tracking-normal">
            refrescar
        </button>
    </p>

    @if ($campaign->followups_enabled && $campaign->api_key_retained_for_followups)
        <div class="mb-8 flex items-start gap-3 p-4 rounded-xl bg-copper/5 border border-copper/30">
            <svg class="w-4 h-4 text-copper mt-0.5 shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-[Fredoka] font-semibold">Follow-ups activos</p>
                <p class="text-xs text-navy/55 font-mono mt-1">
                    máx {{ $campaign->followup_max_attempts }} intentos · cooldown {{ $campaign->followup_cooldown_hours }}h
                    @if ($campaign->api_key_retention_expires_at)
                        · expira {{ $campaign->api_key_retention_expires_at->translatedFormat('d M') }}
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('campaigns.stop-followups', $campaign) }}">
                @csrf
                <button type="submit" class="text-xs text-navy/55 hover:text-red-700 transition underline underline-offset-4 decoration-navy/20 hover:decoration-red-700">
                    Detener secuencia
                </button>
            </form>
        </div>
    @endif

    {{-- ═══ Funnel ═══ --}}
    <section class="mb-12">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Funnel de conversión</p>

        <div class="space-y-3">
            @foreach ($funnel as $i => $stage)
                @php
                    $width = max(4, (float) $stage['pct_total']);
                    $isFinal = ($i === count($funnel) - 1);
                @endphp
                <div class="grid grid-cols-12 gap-3 sm:gap-4 items-center">
                    <div class="col-span-3 sm:col-span-2 text-xs sm:text-sm text-navy/60 truncate">{{ $stage['label'] }}</div>
                    <div class="col-span-6 sm:col-span-8 relative h-9">
                        <div class="absolute inset-y-0 left-0 rounded-lg {{ $isFinal ? 'bg-copper' : 'bg-navy' }} transition-all duration-700 ease-out" style="width: {{ $width }}%;"></div>
                        <div class="absolute inset-0 flex items-center px-3">
                            <span class="text-[11px] font-mono {{ $width > 20 ? 'text-cream' : 'text-navy/60' }} transition-colors">
                                {{ $stage['pct_total'] }}%
                                @if ($i > 0)
                                    <span class="{{ $width > 20 ? 'text-cream/60' : 'text-navy/35' }} ml-1">· {{ $stage['pct_prev'] }}% del anterior</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="col-span-3 sm:col-span-2 text-right font-[Fredoka] font-semibold text-lg sm:text-xl {{ $isFinal ? 'text-copper' : 'text-navy' }} tabular-nums">
                        {{ $stage['count'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══ Time series ═══ --}}
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-4">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em]">Engagement en el tiempo</p>
            <div class="flex items-center gap-4 text-[11px] text-navy/50">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-0.5 bg-navy"></span> Opens
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-0.5 bg-copper"></span> Clicks
                </span>
            </div>
        </div>

        @if (! $timeSeries['has_enough_data'])
            <div class="h-24 flex items-center justify-center text-xs text-navy/45 italic border border-dashed border-navy/15 rounded-xl">
                Datos insuficientes — vuelve en unos minutos.
            </div>
        @else
            @php
                $buckets = $timeSeries['buckets'];
                $count = count($buckets);
                $maxY = 1;
                foreach ($buckets as $b) {
                    $maxY = max($maxY, $b['opens'], $b['clicks']);
                }
                $vbW = 600;
                $vbH = 160;
                $padX = 8;
                $padY = 14;
                $usableW = $vbW - 2 * $padX;
                $usableH = $vbH - 2 * $padY;
                $stepX = $count > 1 ? $usableW / ($count - 1) : 0;

                $pathFor = function (string $key) use ($buckets, $count, $padX, $padY, $usableH, $stepX, $maxY) {
                    $parts = [];
                    foreach ($buckets as $i => $b) {
                        $x = $padX + $stepX * $i;
                        $y = $padY + $usableH - ($b[$key] / $maxY) * $usableH;
                        $parts[] = ($i === 0 ? 'M' : 'L').number_format($x, 2, '.', '').','.number_format($y, 2, '.', '');
                    }

                    return implode(' ', $parts);
                };

                $opensPath = $pathFor('opens');
                $clicksPath = $pathFor('clicks');
            @endphp

            <div class="relative w-full">
                <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" preserveAspectRatio="none" class="w-full h-32 sm:h-40">
                    {{-- baseline --}}
                    <line x1="{{ $padX }}" y1="{{ $vbH - $padY }}" x2="{{ $vbW - $padX }}" y2="{{ $vbH - $padY }}" stroke="#1a1a2e" stroke-opacity="0.1" stroke-width="1" />
                    {{-- opens line --}}
                    <path d="{{ $opensPath }}" fill="none" stroke="#1a1a2e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    {{-- clicks line --}}
                    <path d="{{ $clicksPath }}" fill="none" stroke="#c8956c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex justify-between text-[10px] text-navy/35 font-mono mt-1">
                    <span>{{ $buckets[0]['label'] ?? '' }}</span>
                    <span>{{ $buckets[$count - 1]['label'] ?? '' }}</span>
                </div>
            </div>
        @endif
    </section>

    {{-- ═══ Follow-up attempt performance ═══ --}}
    @if (! empty($followupPerformance))
        <section class="mb-12">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Performance por intento</p>

            <div class="space-y-4">
                @foreach ($followupPerformance as $row)
                    <div>
                        <div class="flex items-baseline justify-between mb-1.5">
                            <p class="text-sm font-[Fredoka] font-semibold">
                                Intento {{ $row['attempt'] }}
                                <span class="text-navy/40 font-mono text-xs ml-1">· {{ $row['sent'] }} envíos</span>
                            </p>
                            <p class="text-xs font-mono text-navy/50">
                                {{ $row['open_rate'] }}% opens · <span class="text-copper">{{ $row['click_rate'] }}% clicks</span>
                            </p>
                        </div>
                        <div class="relative h-3 bg-navy/5 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-navy/60 rounded-full" style="width: {{ $row['open_rate'] }}%;"></div>
                            <div class="absolute inset-y-0 left-0 bg-copper rounded-full" style="width: {{ $row['click_rate'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══ Geo + demographics ═══ --}}
    @if (! empty($countryBreakdown) || ! empty($segmentBreakdown['age_range']) || ! empty($segmentBreakdown['gender']))
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div>
                <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Top países de origen</p>
                @if (empty($countryBreakdown))
                    <p class="text-xs text-navy/45 italic">Sin datos todavía.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($countryBreakdown as $row)
                            <div>
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-sm font-[Fredoka] font-semibold">{{ $row['country'] }}</span>
                                    <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                </div>
                                <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-copper rounded-full" style="width: {{ $row['pct'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Por rango de edad</p>
                @if (empty($segmentBreakdown['age_range']))
                    <p class="text-xs text-navy/45 italic">Sin datos todavía.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($segmentBreakdown['age_range'] as $row)
                            <div>
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-sm font-[Fredoka] font-semibold">{{ $row['label'] }}</span>
                                    <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                </div>
                                <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-navy/70 rounded-full" style="width: {{ $row['pct'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Por género</p>
                @if (empty($segmentBreakdown['gender']))
                    <p class="text-xs text-navy/45 italic">Sin datos todavía.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($segmentBreakdown['gender'] as $row)
                            <div>
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-sm font-[Fredoka] font-semibold">{{ $row['label'] }}</span>
                                    <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                </div>
                                <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-navy/70 rounded-full" style="width: {{ $row['pct'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ═══ Summary metrics (footer) ═══ --}}
    <section class="mb-10 pt-8 border-t border-navy/10">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-5">Resumen</p>
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-6">
            <div>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none">{{ $stats['sent'] }}</dd>
                <dt class="text-xs text-navy/45 mt-1.5">Enviados</dt>
            </div>
            <div>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none">{{ $stats['open_rate'] }}<span class="text-sm text-navy/35">%</span></dd>
                <dt class="text-xs text-navy/45 mt-1.5">Open rate · {{ $stats['opened'] }}</dt>
            </div>
            <div>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-copper">{{ $stats['click_rate'] }}<span class="text-sm text-navy/35">%</span></dd>
                <dt class="text-xs text-navy/45 mt-1.5">CTR · {{ $stats['clicked'] }}</dt>
            </div>
            <div>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-emerald-700">{{ $stats['conversion_rate'] }}<span class="text-sm text-navy/35">%</span></dd>
                <dt class="text-xs text-navy/45 mt-1.5">Conversión · {{ $stats['converted'] }}</dt>
            </div>
        </dl>

        @if ($stats['bounced'] > 0 || $stats['failed'] > 0 || $stats['unsubscribed'] > 0)
            <p class="text-xs text-navy/50 font-mono mt-5">
                @if ($stats['bounced'] > 0) {{ $stats['bounced'] }} rebotes @endif
                @if ($stats['failed'] > 0) · {{ $stats['failed'] }} fallos @endif
                @if ($stats['unsubscribed'] > 0) · {{ $stats['unsubscribed'] }} bajas @endif
            </p>
        @endif
    </section>

    {{-- ═══ Recipient table ═══ --}}
    @if ($recipients->isNotEmpty())
        <section>
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Destinatarios</p>
            <div class="border-t border-navy/10">
                @foreach ($recipients as $r)
                    <div class="grid grid-cols-12 gap-3 py-3 border-b border-navy/10 items-center text-sm">
                        <span class="col-span-6 sm:col-span-4 font-mono text-xs text-navy/70 truncate">{{ $r->email }}</span>
                        <span class="col-span-3 sm:col-span-2 text-xs {{ match ($r->status) {
                            'converted' => 'text-emerald-700',
                            'unsubscribed' => 'text-amber-700',
                            'bounced', 'failed' => 'text-red-700',
                            default => 'text-navy/55',
                        } }}">
                            {{ match ($r->status) {
                                'queued' => 'En cola',
                                'sending' => 'Enviando',
                                'sent' => 'Enviado',
                                'bounced' => 'Rebote',
                                'failed' => 'Fallido',
                                'unsubscribed' => 'Baja',
                                'converted' => 'Convertido',
                                default => $r->status,
                            } }}
                        </span>
                        <span class="hidden sm:inline col-span-2 text-xs text-navy/45 font-mono">
                            {{ $r->opens_count }}<span class="text-navy/30">/{{ $r->clicks_count }}</span>
                        </span>
                        <span class="col-span-2 text-xs text-navy/40 font-mono text-right">
                            {{ $r->attempts_sent > 0 ? 'intento '.$r->attempts_sent : '—' }}
                        </span>
                        <form method="POST" action="{{ route('campaigns.recipients.toggle-conversion', ['campaign' => $campaign, 'recipient' => $r]) }}" class="col-span-3 sm:col-span-2 text-right">
                            @csrf
                            <button type="submit" class="text-[11px] text-navy/45 hover:text-navy transition underline underline-offset-4 decoration-navy/20">
                                {{ $r->isConverted() ? 'Deshacer' : 'Convertido' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            @if ($recipients->hasPages())
                <div class="mt-4 text-xs text-navy/45 font-mono">
                    {{ $recipients->links() }}
                </div>
            @endif
        </section>
    @endif
</div>
