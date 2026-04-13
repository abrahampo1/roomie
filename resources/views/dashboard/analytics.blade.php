<x-layouts.app title="Analíticas">
    <header class="pb-7 sm:pb-8 mb-2">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Analíticas</h1>
        </div>
    </header>

    <x-dashboard.nav-tabs active="analytics" />

    {{-- Summary KPIs --}}
    <section class="mb-10 sm:mb-12">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-6">
            <x-dashboard.kpi-card :value="$kpis['total_sent']" label="Emails enviados" />
            <x-dashboard.kpi-card :value="$kpis['open_rate']" suffix="%" label="Open rate" />
            <x-dashboard.kpi-card :value="$kpis['click_rate']" suffix="%" label="CTR" color="text-copper" />
            <x-dashboard.kpi-card :value="$kpis['conversion_rate']" suffix="%" label="Conversión" color="text-emerald-700" />
        </dl>
    </section>

    {{-- Cross-campaign funnel --}}
    @if ($kpis['total_recipients'] > 0)
        <section class="mb-12">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Funnel de conversión global</p>
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
                                <span class="text-[11px] font-mono {{ $width > 20 ? 'text-cream' : 'text-navy/60' }}">
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
    @endif

    {{-- Time series --}}
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-4">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em]">Actividad — últimos 30 días</p>
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
                Sin actividad en los últimos 30 días.
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
                <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" preserveAspectRatio="none" class="block w-full h-32 sm:h-40">
                    <line x1="{{ $padX }}" y1="{{ $vbH - $padY }}" x2="{{ $vbW - $padX }}" y2="{{ $vbH - $padY }}" stroke="#1a1a2e" stroke-opacity="0.1" stroke-width="1" />
                    <path d="{{ $opensPath }}" fill="none" stroke="#1a1a2e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="{{ $clicksPath }}" fill="none" stroke="#c8956c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex justify-between text-[10px] text-navy/35 font-mono mt-1">
                    <span>{{ $buckets[0]['label'] ?? '' }}</span>
                    <span>{{ $buckets[$count - 1]['label'] ?? '' }}</span>
                </div>
            </div>
        @endif
    </section>

    {{-- Geo + demographics --}}
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
</x-layouts.app>
