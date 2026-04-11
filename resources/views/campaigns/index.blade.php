<x-layouts.app title="Campañas">
    <header class="flex items-end justify-between pb-7 sm:pb-8 mb-9 sm:mb-10 border-b border-navy/15 gap-4 flex-wrap">
        <div>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Campañas</h1>
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
        <div class="py-20 sm:py-24 text-center">
            <svg class="w-7 h-7 text-navy/15 mx-auto mb-5" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55">Todavía no hay campañas en el archivo.</p>
            <a href="{{ route('campaigns.create') }}" class="inline-block mt-4 text-sm underline underline-offset-4 decoration-navy/20 hover:decoration-navy transition py-2 -my-2">
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
                       class="block py-5 group hover:bg-navy/[0.025] active:bg-navy/[0.04] transition -mx-3 px-3 rounded-lg">
                        <div class="flex items-center gap-3 mb-1.5">
                            <span class="font-mono text-[11px] text-navy/35">
                                #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[11px] ml-auto
                                {{ $campaign->status === 'completed' ? 'text-emerald-700' : '' }}
                                {{ $campaign->status === 'processing' ? 'text-amber-700' : '' }}
                                {{ $campaign->status === 'pending' ? 'text-navy/55' : '' }}
                                {{ $campaign->status === 'failed' ? 'text-red-700' : '' }}">
                                {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                            </span>
                            @if ($campaign->quality_score)
                                <span class="font-[Fredoka] font-semibold text-sm shrink-0
                                    {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                                    {{ $campaign->quality_score }}<span class="text-navy/30 font-normal text-[11px]">/100</span>
                                </span>
                            @endif
                        </div>
                        <p class="font-[Fredoka] font-semibold text-base sm:text-[17px] leading-snug group-hover:text-copper-dark transition line-clamp-2 sm:truncate">
                            {{ $campaign->name ?? $campaign->strategy['campaign_name'] ?? $campaign->objective }}
                        </p>
                        <p class="text-sm text-navy/50 mt-1 line-clamp-2 sm:truncate">{{ $campaign->objective }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
