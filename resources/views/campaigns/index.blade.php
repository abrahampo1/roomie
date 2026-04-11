<x-layouts.app title="Campañas">
    <header class="flex items-end justify-between pb-8 mb-10 border-b border-navy/15 gap-6 flex-wrap">
        <div>
            <h1 class="font-[Fredoka] font-semibold text-4xl tracking-tight">Campañas</h1>
            <p class="text-sm text-navy/55 mt-2">
                {{ $campaigns->count() }} {{ $campaigns->count() === 1 ? 'campaña generada' : 'campañas generadas' }} hasta hoy.
            </p>
        </div>
        <a href="{{ route('campaigns.create') }}"
           class="inline-flex items-center gap-2 bg-navy text-cream pl-5 pr-4 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
            Nueva campaña
            <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
        </a>
    </header>

    @if ($campaigns->isEmpty())
        <div class="py-24 text-center">
            <svg class="w-7 h-7 text-navy/15 mx-auto mb-5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55">Todavía no hay campañas en el archivo.</p>
            <a href="{{ route('campaigns.create') }}" class="inline-block mt-4 text-sm underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition">
                Crear la primera
            </a>
        </div>
    @else
        @php
            $statusLabels = [
                'completed' => 'Completada',
                'processing' => 'En curso',
                'pending' => 'En cola',
                'failed' => 'Fallida',
            ];
        @endphp
        <ul class="divide-y divide-navy/10">
            @foreach ($campaigns as $campaign)
                <li>
                    <a href="{{ route('campaigns.show', $campaign) }}"
                       class="grid grid-cols-12 gap-4 py-5 group hover:bg-navy/[0.025] transition -mx-3 px-3 rounded-lg">
                        <span class="col-span-2 md:col-span-1 font-mono text-xs text-navy/35 pt-1.5">
                            #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="col-span-10 md:col-span-7 min-w-0">
                            <p class="font-[Fredoka] font-semibold text-base truncate group-hover:text-copper-dark transition">
                                {{ $campaign->strategy['campaign_name'] ?? $campaign->objective }}
                            </p>
                            <p class="text-sm text-navy/50 truncate mt-0.5">{{ $campaign->objective }}</p>
                        </div>
                        <div class="col-span-6 md:col-span-2 text-left md:text-right pt-1">
                            @if ($campaign->quality_score)
                                <p class="font-[Fredoka] font-semibold text-base
                                    {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                                    {{ $campaign->quality_score }}<span class="text-navy/30 font-normal text-xs">/100</span>
                                </p>
                            @endif
                        </div>
                        <div class="col-span-6 md:col-span-2 text-right pt-2 text-xs">
                            <span
                                class="{{ $campaign->status === 'completed' ? 'text-emerald-700' : '' }}
                                {{ $campaign->status === 'processing' ? 'text-amber-700' : '' }}
                                {{ $campaign->status === 'pending' ? 'text-navy/55' : '' }}
                                {{ $campaign->status === 'failed' ? 'text-red-700' : '' }}">
                                {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                            </span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
