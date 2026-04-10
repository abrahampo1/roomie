<x-layouts.app title="Campañas">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Campañas</h1>
            <p class="text-navy/50 mt-1">Historial de campañas generadas por IA</p>
        </div>
        <a href="{{ route('campaigns.create') }}"
           class="bg-navy text-sand-light px-5 py-2.5 rounded-xl font-medium hover:bg-navy-light transition">
            Nueva campaña
        </a>
    </div>

    @if ($campaigns->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-sand">
            <img src="{{ asset('images/brand/LogoRoomie_Estrella.svg') }}" alt="Roomie" class="w-12 h-12 opacity-20 mx-auto mb-4">
            <p class="text-navy/40 mb-4">Aún no hay campañas generadas</p>
            <a href="{{ route('campaigns.create') }}" class="text-sm font-medium text-navy underline underline-offset-4">Crear la primera</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($campaigns as $campaign)
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="block bg-white rounded-xl border border-sand p-5 hover:shadow-md hover:border-navy/20 transition group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate group-hover:text-navy transition">
                                {{ $campaign->strategy['campaign_name'] ?? $campaign->objective }}
                            </p>
                            <p class="text-sm text-navy/40 mt-1 truncate">{{ $campaign->objective }}</p>
                        </div>
                        <div class="flex items-center gap-3 ml-4 shrink-0">
                            @if ($campaign->quality_score)
                                <span class="text-sm font-semibold
                                    {{ $campaign->quality_score >= 80 ? 'text-emerald-600' : ($campaign->quality_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $campaign->quality_score }}/100
                                </span>
                            @endif
                            <span class="text-xs px-2.5 py-1 rounded-full font-medium
                                {{ $campaign->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                {{ $campaign->status === 'processing' ? 'bg-amber-50 text-amber-700' : '' }}
                                {{ $campaign->status === 'pending' ? 'bg-navy/5 text-navy/50' : '' }}
                                {{ $campaign->status === 'failed' ? 'bg-red-50 text-red-700' : '' }}">
                                {{ $campaign->status }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
