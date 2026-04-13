<x-layouts.dashboard title="Analiticas" active="analytics">

    {{-- ═══ Header ═══ --}}
    <header class="mb-8 sm:mb-10">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-2">Panel de control</p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight text-navy">Analiticas</h1>
    </header>

    {{-- ═══ KPI cards ═══ --}}
    <section class="mb-10 sm:mb-12">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-4">Metricas clave</p>
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            {{-- Emails enviados --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Emails enviados</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ number_format($kpis['total_sent']) }}
                </dd>
                <p class="text-[10px] font-mono text-navy/35 mt-2">
                    {{ number_format($kpis['total_recipients']) }} destinatarios totales
                </p>
            </div>

            {{-- Open rate --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Open rate</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ $kpis['open_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <p class="text-[10px] font-mono text-navy/35 mt-2">
                    {{ number_format($kpis['total_opened']) }} abiertos
                </p>
            </div>

            {{-- CTR --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">CTR</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-copper tabular-nums">
                    {{ $kpis['click_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <p class="text-[10px] font-mono text-navy/35 mt-2">
                    {{ number_format($kpis['total_clicked']) }} clicks
                </p>
            </div>

            {{-- Conversion --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Conversion</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-emerald-700 tabular-nums">
                    {{ $kpis['conversion_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <p class="text-[10px] font-mono text-navy/35 mt-2">
                    {{ number_format($kpis['total_converted']) }} conversiones
                </p>
            </div>
        </div>
    </section>

    {{-- ═══ Funnel + Summary (two-column) ═══ --}}
    @if ($kpis['total_recipients'] > 0)
        <section class="mb-10 sm:mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Left: Funnel (col-span-2) --}}
                <div class="lg:col-span-2 rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-5">Funnel de conversion global</p>

                    <div class="space-y-3">
                        @foreach ($funnel as $i => $stage)
                            @php
                                $width = max(4, (float) $stage['pct_total']);
                                $isFinal = ($i === count($funnel) - 1);
                            @endphp
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-24 sm:w-28 text-xs sm:text-sm text-navy/60 truncate shrink-0">{{ $stage['label'] }}</div>
                                <div class="flex-1 relative h-9">
                                    <div class="absolute inset-y-0 left-0 rounded-lg {{ $isFinal ? 'bg-copper' : 'bg-navy' }} transition-all duration-700 ease-out" style="width: {{ $width }}%;"></div>
                                    <div class="absolute inset-0 flex items-center px-3">
                                        <span class="text-[11px] font-mono {{ $width > 20 ? 'text-cream' : 'text-navy/60' }}">
                                            {{ $stage['pct_total'] }}%
                                            @if ($i > 0)
                                                <span class="{{ $width > 20 ? 'text-cream/60' : 'text-navy/35' }} ml-1">{{ $stage['pct_prev'] }}% del anterior</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="w-16 sm:w-20 text-right font-[Fredoka] font-semibold text-lg sm:text-xl {{ $isFinal ? 'text-copper' : 'text-navy' }} tabular-nums shrink-0">
                                    {{ number_format($stage['count']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Summary rates (col-span-1) --}}
                <div class="lg:col-span-1 rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 flex flex-col justify-between">
                    <div>
                        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-5">Resumen de tasas</p>

                        <dl class="space-y-5">
                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Tasa de apertura</dt>
                                <dd class="font-[Fredoka] font-semibold text-3xl leading-none text-navy tabular-nums">
                                    {{ $kpis['open_rate'] }}<span class="text-sm text-navy/35">%</span>
                                </dd>
                                <div class="mt-2 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-navy rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['open_rate']) }}%;"></div>
                                </div>
                            </div>

                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Tasa de click</dt>
                                <dd class="font-[Fredoka] font-semibold text-3xl leading-none text-copper tabular-nums">
                                    {{ $kpis['click_rate'] }}<span class="text-sm text-navy/35">%</span>
                                </dd>
                                <div class="mt-2 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-copper rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['click_rate']) }}%;"></div>
                                </div>
                            </div>

                            <div>
                                <dt class="text-xs text-navy/45 mb-1">Tasa de conversion</dt>
                                <dd class="font-[Fredoka] font-semibold text-3xl leading-none text-emerald-700 tabular-nums">
                                    {{ $kpis['conversion_rate'] }}<span class="text-sm text-navy/35">%</span>
                                </dd>
                                <div class="mt-2 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-600 rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['conversion_rate']) }}%;"></div>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <p class="text-[10px] font-mono text-navy/30 mt-6 pt-4 border-t border-navy/10">
                        {{ number_format($kpis['total_recipients']) }} destinatarios en total
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══ Time series chart ═══ --}}
    <section class="mb-10 sm:mb-12">
        <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
            <div class="flex items-baseline justify-between mb-5">
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40">Actividad — ultimos 30 dias</p>
                <div class="flex items-center gap-4 text-[11px] text-navy/50">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-0.5 bg-navy rounded-full"></span> Opens
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-0.5 bg-copper rounded-full"></span> Clicks
                    </span>
                </div>
            </div>

            @if (! $timeSeries['has_enough_data'])
                <div class="h-32 sm:h-40 flex items-center justify-center text-xs text-navy/45 italic border border-dashed border-navy/15 rounded-xl">
                    Sin actividad en los ultimos 30 dias.
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
                            $parts[] = ($i === 0 ? 'M' : 'L') . number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
                        }
                        return implode(' ', $parts);
                    };

                    $opensPath = $pathFor('opens');
                    $clicksPath = $pathFor('clicks');

                    $pointPcts = [];
                    for ($i = 0; $i < $count; $i++) {
                        $pointPcts[$i] = $count > 1 ? ($padX + $stepX * $i) / $vbW * 100 : 50;
                    }
                @endphp

                <div class="relative w-full">
                    <div class="relative">
                        <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" preserveAspectRatio="none" class="block w-full h-32 sm:h-40">
                            {{-- Grid lines --}}
                            @for ($g = 0; $g < 4; $g++)
                                <line
                                    x1="{{ $padX }}"
                                    y1="{{ $padY + ($usableH / 3) * $g }}"
                                    x2="{{ $vbW - $padX }}"
                                    y2="{{ $padY + ($usableH / 3) * $g }}"
                                    stroke="#1a1a2e"
                                    stroke-opacity="0.06"
                                    stroke-width="0.5"
                                />
                            @endfor
                            {{-- Baseline --}}
                            <line x1="{{ $padX }}" y1="{{ $vbH - $padY }}" x2="{{ $vbW - $padX }}" y2="{{ $vbH - $padY }}" stroke="#1a1a2e" stroke-opacity="0.1" stroke-width="1" />
                            {{-- Opens area fill --}}
                            <path d="{{ $opensPath }} L{{ number_format($padX + $stepX * ($count - 1), 2, '.', '') }},{{ $vbH - $padY }} L{{ $padX }},{{ $vbH - $padY }} Z" fill="#1a1a2e" fill-opacity="0.04" />
                            {{-- Opens line --}}
                            <path d="{{ $opensPath }}" fill="none" stroke="#1a1a2e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            {{-- Clicks line --}}
                            <path d="{{ $clicksPath }}" fill="none" stroke="#c8956c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        {{-- Hover zones --}}
                        @foreach ($buckets as $i => $b)
                            @php
                                $pointPct = $pointPcts[$i];
                                $zoneLeft = $i === 0 ? 0 : ($pointPcts[$i - 1] + $pointPct) / 2;
                                $zoneRight = $i === $count - 1 ? 100 : ($pointPct + $pointPcts[$i + 1]) / 2;
                                $zoneWidth = max(0.01, $zoneRight - $zoneLeft);
                                $dotLeftInZone = ($pointPct - $zoneLeft) / $zoneWidth * 100;
                                $opensTopPct = ($padY + $usableH - ($b['opens'] / $maxY) * $usableH) / $vbH * 100;
                                $clicksTopPct = ($padY + $usableH - ($b['clicks'] / $maxY) * $usableH) / $vbH * 100;
                                $tooltipClass = $i <= 1
                                    ? ''
                                    : ($i >= $count - 2 ? '-translate-x-full' : '-translate-x-1/2');
                            @endphp
                            <div class="absolute top-0 bottom-0 group/point" style="left: {{ $zoneLeft }}%; width: {{ $zoneWidth }}%;">
                                <div class="absolute top-0 bottom-0 w-px bg-navy/20 opacity-0 group-hover/point:opacity-100 transition-opacity pointer-events-none" style="left: {{ $dotLeftInZone }}%; transform: translateX(-50%);"></div>
                                <div class="absolute w-2 h-2 rounded-full bg-navy opacity-0 group-hover/point:opacity-100 transition-opacity pointer-events-none" style="left: {{ $dotLeftInZone }}%; top: {{ $opensTopPct }}%; transform: translate(-50%, -50%);"></div>
                                <div class="absolute w-2 h-2 rounded-full bg-copper opacity-0 group-hover/point:opacity-100 transition-opacity pointer-events-none" style="left: {{ $dotLeftInZone }}%; top: {{ $clicksTopPct }}%; transform: translate(-50%, -50%);"></div>
                                <div class="absolute top-1 pointer-events-none opacity-0 group-hover/point:opacity-100 transition-opacity z-20 {{ $tooltipClass }}" style="left: {{ $dotLeftInZone }}%;">
                                    <div class="bg-navy text-cream rounded-lg px-3 py-2 shadow-lg whitespace-nowrap">
                                        <p class="text-cream/60 text-[9px] font-mono uppercase tracking-wider mb-1.5">{{ $b['label'] }}</p>
                                        <div class="flex items-center gap-4 text-[11px]">
                                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-px bg-cream"></span>Opens</span>
                                            <span class="tabular-nums font-mono ml-auto">{{ $b['opens'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-4 text-[11px]">
                                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-px bg-copper"></span>Clicks</span>
                                            <span class="tabular-nums font-mono ml-auto">{{ $b['clicks'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between text-[10px] text-navy/35 font-mono mt-1">
                        <span>{{ $buckets[0]['label'] ?? '' }}</span>
                        @if ($count > 2)
                            <span>{{ $buckets[intdiv($count, 2)]['label'] ?? '' }}</span>
                        @endif
                        <span>{{ $buckets[$count - 1]['label'] ?? '' }}</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ═══ Breakdowns (three-column) ═══ --}}
    @if (! empty($countryBreakdown) || ! empty($segmentBreakdown['age_range']) || ! empty($segmentBreakdown['gender']))
        <section class="mb-10 sm:mb-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Country breakdown --}}
                <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-5">Top paises de origen</p>

                    @if (empty($countryBreakdown))
                        <p class="text-xs text-navy/45 italic">Sin datos todavia.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($countryBreakdown as $row)
                                <div>
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-sm font-[Fredoka] font-semibold text-navy">{{ $row['country'] }}</span>
                                        <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                    </div>
                                    <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-copper rounded-full transition-all duration-500" style="width: {{ $row['pct'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Age range breakdown --}}
                <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-5">Por rango de edad</p>

                    @if (empty($segmentBreakdown['age_range']))
                        <p class="text-xs text-navy/45 italic">Sin datos todavia.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($segmentBreakdown['age_range'] as $row)
                                <div>
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-sm font-[Fredoka] font-semibold text-navy">{{ $row['label'] }}</span>
                                        <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                    </div>
                                    <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-navy/70 rounded-full transition-all duration-500" style="width: {{ $row['pct'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Gender breakdown --}}
                <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                    <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-5">Por genero</p>

                    @if (empty($segmentBreakdown['gender']))
                        <p class="text-xs text-navy/45 italic">Sin datos todavia.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($segmentBreakdown['gender'] as $row)
                                <div>
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-sm font-[Fredoka] font-semibold text-navy">{{ $row['label'] }}</span>
                                        <span class="text-xs font-mono text-navy/50">{{ $row['opened'] }}<span class="text-navy/30">/{{ $row['sent'] }}</span></span>
                                    </div>
                                    <div class="h-1.5 bg-navy/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-navy/70 rounded-full transition-all duration-500" style="width: {{ $row['pct'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

</x-layouts.dashboard>
