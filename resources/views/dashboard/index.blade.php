<x-layouts.dashboard title="Panel" active="index">

    {{-- Header --}}
    <header class="pb-7 sm:pb-8 mb-2">
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-2">Panel de control</p>
                <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Panel de control</h1>
            </div>
            <a href="{{ route('campaigns.create') }}"
               class="inline-flex items-center gap-2 bg-navy text-cream pl-5 pr-4 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                Nueva campaña
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </a>
        </div>
    </header>

    {{-- KPI Row 1: headline metrics --}}
    <section class="mb-6">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-4">Resumen general</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Campañas totales --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Campañas totales</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ $kpis['total_campaigns'] }}
                </dd>
                <p class="font-mono text-[10px] text-navy/35 mt-2">
                    {{ $kpis['completed_campaigns'] }} completadas
                    @if ($kpis['failed_campaigns'] > 0)
                        <span class="text-red-700/60">· {{ $kpis['failed_campaigns'] }} fallidas</span>
                    @endif
                </p>
            </div>

            {{-- Open Rate --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Open Rate</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ $kpis['open_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <div class="mt-3 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                    <div class="h-full bg-navy/70 rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['open_rate']) }}%;"></div>
                </div>
            </div>

            {{-- CTR --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">CTR</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-copper tabular-nums">
                    {{ $kpis['click_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <div class="mt-3 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                    <div class="h-full bg-copper rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['click_rate']) }}%;"></div>
                </div>
            </div>

            {{-- Conversión --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Conversión</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-emerald-700 tabular-nums">
                    {{ $kpis['conversion_rate'] }}<span class="text-sm text-navy/35">%</span>
                </dd>
                <div class="mt-3 h-1.5 bg-navy/5 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-600 rounded-full transition-all duration-700" style="width: {{ min(100, $kpis['conversion_rate']) }}%;"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Row 2: volume metrics --}}
    <section class="mb-10 sm:mb-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Emails enviados --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Emails enviados</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ number_format($kpis['total_sent']) }}
                </dd>
                <p class="font-mono text-[10px] text-navy/35 mt-2">
                    {{ number_format($kpis['total_recipients']) }} destinatarios
                </p>
            </div>

            {{-- Abiertos --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Abiertos</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-navy tabular-nums">
                    {{ number_format($kpis['total_opened']) }}
                </dd>
                <p class="font-mono text-[10px] text-navy/35 mt-2">
                    de {{ number_format($kpis['total_sent']) }} enviados
                </p>
            </div>

            {{-- Clicks --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Clicks</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none text-copper tabular-nums">
                    {{ number_format($kpis['total_clicked']) }}
                </dd>
                <p class="font-mono text-[10px] text-navy/35 mt-2">
                    {{ number_format($kpis['total_converted']) }} convertidos
                </p>
            </div>

            {{-- Calidad media --}}
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
                <dt class="text-xs text-navy/45 mb-2">Calidad media</dt>
                <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none tabular-nums {{ $kpis['avg_quality_score'] !== null ? ($kpis['avg_quality_score'] >= 80 ? 'text-emerald-700' : ($kpis['avg_quality_score'] >= 60 ? 'text-amber-700' : 'text-red-700')) : 'text-navy/30' }}">
                    {{ $kpis['avg_quality_score'] ?? '—' }}@if($kpis['avg_quality_score'])<span class="text-sm text-navy/35">/100</span>@endif
                </dd>
                <p class="font-mono text-[10px] text-navy/35 mt-2">
                    @if ($kpis['total_unsubscribed'] > 0)
                        {{ $kpis['total_unsubscribed'] }} bajas registradas
                    @else
                        sin bajas registradas
                    @endif
                </p>
            </div>
        </div>
    </section>

    {{-- Funnel --}}
    @if ($kpis['total_recipients'] > 0)
        <section class="mb-10 sm:mb-12">
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-4">Funnel de conversión global</p>
            <div class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6">
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
                                        @if ($i > 0)
                                            <span class="{{ $width > 20 ? 'text-cream/60' : 'text-navy/35' }} ml-1">· {{ $stage['pct_prev'] }}% del anterior</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-span-3 sm:col-span-2 text-right font-[Fredoka] font-semibold text-lg {{ $isFinal ? 'text-copper' : 'text-navy' }} tabular-nums">
                                {{ $stage['count'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Top campaigns table --}}
    @if (! empty($topCampaigns))
        <section class="mb-10 sm:mb-12">
            <div class="flex items-baseline justify-between mb-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40">Top campañas por conversión</p>
                <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/55 hover:text-navy transition underline underline-offset-4 decoration-navy/20">
                    Ver todas
                </a>
            </div>
            <div class="rounded-2xl border border-navy/10 bg-white overflow-hidden">
                {{-- Table header --}}
                <div class="hidden sm:grid grid-cols-12 gap-3 px-5 sm:px-6 py-3 border-b border-navy/10 text-[10px] font-mono text-navy/40 uppercase tracking-wider">
                    <span class="col-span-1">#</span>
                    <span class="col-span-3">Nombre</span>
                    <span class="col-span-2">Estado</span>
                    <span class="col-span-1 text-right">Calidad</span>
                    <span class="col-span-2 text-right">Enviados</span>
                    <span class="col-span-1 text-right">Conv.</span>
                    <span class="col-span-2 text-right">Conv. Rate</span>
                </div>

                {{-- Table rows --}}
                <div class="divide-y divide-navy/5">
                    @foreach ($topCampaigns as $tc)
                        @php
                            $statusLabels = [
                                'completed' => 'Completada',
                                'processing' => 'En curso',
                                'pending' => 'En cola',
                                'failed' => 'Fallida',
                            ];
                            $statusColors = [
                                'completed' => 'text-emerald-700',
                                'processing' => 'text-amber-700',
                                'pending' => 'text-navy/55',
                                'failed' => 'text-red-700',
                            ];
                        @endphp
                        <a href="{{ route('campaigns.show', $tc['id']) }}"
                           class="grid grid-cols-12 gap-3 px-5 sm:px-6 py-3.5 items-center hover:bg-sand-light/50 transition text-sm">
                            <span class="col-span-4 sm:col-span-1 font-mono text-[11px] text-navy/35">
                                {{ str_pad($tc['id'], 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="col-span-8 sm:col-span-3 font-[Fredoka] font-semibold text-sm truncate">
                                {{ $tc['name'] ?: Str::limit($tc['objective'], 35) }}
                            </span>
                            <span class="col-span-4 sm:col-span-2 text-xs {{ $statusColors[$tc['status']] ?? 'text-navy/55' }}">
                                {{ $statusLabels[$tc['status']] ?? $tc['status'] }}
                            </span>
                            <span class="col-span-2 sm:col-span-1 text-right font-[Fredoka] font-semibold text-sm
                                {{ $tc['quality_score'] !== null ? ($tc['quality_score'] >= 80 ? 'text-emerald-700' : ($tc['quality_score'] >= 60 ? 'text-amber-700' : 'text-red-700')) : 'text-navy/30' }}">
                                {{ $tc['quality_score'] ?? '—' }}
                            </span>
                            <span class="col-span-2 text-right font-mono text-xs text-navy/55 tabular-nums">
                                {{ number_format($tc['sent']) }}
                            </span>
                            <span class="col-span-2 sm:col-span-1 text-right font-mono text-xs text-navy/55 tabular-nums">
                                {{ number_format($tc['converted']) }}
                            </span>
                            <span class="col-span-2 text-right font-[Fredoka] font-semibold text-sm text-copper tabular-nums">
                                {{ $tc['conversion_rate'] }}%
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Recent campaigns grid --}}
    <section class="mb-10 sm:mb-12">
        <div class="flex items-baseline justify-between mb-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40">Campañas recientes</p>
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

    {{-- Quick links --}}
    <section>
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-4">Accesos directos</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            {{-- Marca --}}
            <a href="{{ route('settings.brand.show') }}"
               class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 hover:border-navy/25 transition group">
                <svg class="w-5 h-5 text-navy/30 group-hover:text-copper transition mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>
                <p class="font-[Fredoka] font-semibold text-sm">Marca</p>
                <p class="text-[11px] text-navy/45 mt-0.5">Identidad visual y tono</p>
            </a>

            {{-- Imágenes --}}
            <a href="{{ route('settings.image-bank.index') }}"
               class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 hover:border-navy/25 transition group">
                <svg class="w-5 h-5 text-navy/30 group-hover:text-copper transition mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <p class="font-[Fredoka] font-semibold text-sm">Imágenes</p>
                <p class="text-[11px] text-navy/45 mt-0.5">Banco de imágenes</p>
            </a>

            {{-- Agentes --}}
            <a href="{{ route('dashboard.agents') }}"
               class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 hover:border-navy/25 transition group">
                <svg class="w-5 h-5 text-navy/30 group-hover:text-copper transition mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                <p class="font-[Fredoka] font-semibold text-sm">Agentes</p>
                <p class="text-[11px] text-navy/45 mt-0.5">Configuración de agentes IA</p>
            </a>

            {{-- Secuencias --}}
            <a href="{{ route('dashboard.sequences') }}"
               class="rounded-2xl border border-navy/10 bg-white p-5 sm:p-6 hover:border-navy/25 transition group">
                <svg class="w-5 h-5 text-navy/30 group-hover:text-copper transition mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                </svg>
                <p class="font-[Fredoka] font-semibold text-sm">Secuencias</p>
                <p class="text-[11px] text-navy/45 mt-0.5">Cadenas de follow-up</p>
            </a>
        </div>
    </section>

</x-layouts.dashboard>
