<x-layouts.app title="Panel">
    <header class="pb-7 sm:pb-8 mb-2">
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
                <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Dashboard</h1>
            </div>
            <a href="{{ route('campaigns.create') }}"
               class="inline-flex items-center gap-2 bg-navy text-cream pl-5 pr-4 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                Nueva campaña
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </a>
        </div>
    </header>

    <x-dashboard.nav-tabs active="index" />

    {{-- KPIs --}}
    <section class="mb-10 sm:mb-12">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-6">
            <x-dashboard.kpi-card :value="$kpis['total_campaigns']" label="Campañas" />
            <x-dashboard.kpi-card :value="$kpis['open_rate']" suffix="%" label="Open rate global" />
            <x-dashboard.kpi-card :value="$kpis['click_rate']" suffix="%" label="CTR global" color="text-copper" />
            <x-dashboard.kpi-card :value="$kpis['conversion_rate']" suffix="%" label="Conversión global" color="text-emerald-700" />
        </dl>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-6 mt-6 pt-6 border-t border-navy/10">
            <x-dashboard.kpi-card :value="$kpis['total_sent']" label="Emails enviados" />
            <x-dashboard.kpi-card :value="$kpis['total_opened']" label="Abiertos" />
            <x-dashboard.kpi-card :value="$kpis['total_clicked']" label="Clicks" />
            <x-dashboard.kpi-card :value="$kpis['avg_quality_score'] ?? '—'" :suffix="$kpis['avg_quality_score'] ? '/100' : ''" label="Calidad media" />
        </div>
    </section>

    {{-- Mini funnel --}}
    @if ($kpis['total_recipients'] > 0)
        <section class="mb-10 sm:mb-12">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Funnel global</p>
            <div class="space-y-3">
                @foreach ($funnel as $i => $stage)
                    @php
                        $width = max(4, (float) $stage['pct_total']);
                        $isFinal = ($i === count($funnel) - 1);
                    @endphp
                    <div class="grid grid-cols-12 gap-3 sm:gap-4 items-center">
                        <div class="col-span-3 sm:col-span-2 text-xs sm:text-sm text-navy/60 truncate">{{ $stage['label'] }}</div>
                        <div class="col-span-6 sm:col-span-8 relative h-8">
                            <div class="absolute inset-y-0 left-0 rounded-lg {{ $isFinal ? 'bg-copper' : 'bg-navy' }} transition-all duration-700 ease-out" style="width: {{ $width }}%;"></div>
                            <div class="absolute inset-0 flex items-center px-3">
                                <span class="text-[11px] font-mono {{ $width > 20 ? 'text-cream' : 'text-navy/60' }}">
                                    {{ $stage['pct_total'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-span-3 sm:col-span-2 text-right font-[Fredoka] font-semibold text-lg {{ $isFinal ? 'text-copper' : 'text-navy' }} tabular-nums">
                            {{ $stage['count'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Recent campaigns --}}
    <section>
        <div class="flex items-baseline justify-between mb-5">
            <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em]">Campañas recientes</p>
            <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/55 hover:text-navy transition underline underline-offset-4 decoration-navy/20">
                Ver todas
            </a>
        </div>

        @if ($campaigns->isEmpty())
            <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
                <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                <p class="text-navy/55 text-sm">Todavía no hay campañas.</p>
                <a href="{{ route('campaigns.create') }}" class="inline-block mt-3 text-sm underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition">
                    Crear la primera
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($campaigns as $campaign)
                    <x-dashboard.campaign-card :campaign="$campaign" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
