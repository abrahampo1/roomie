@props(['campaign'])

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

<a href="{{ route('campaigns.show', $campaign) }}"
   class="block rounded-2xl border border-navy/15 bg-white p-5 sm:p-6 hover:border-navy/30 transition group">
    <div class="flex items-center gap-3 mb-3">
        <span class="font-mono text-[11px] text-navy/35">#{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}</span>
        <span class="text-[11px] {{ $statusColors[$campaign->status] ?? 'text-navy/55' }}">
            {{ $statusLabels[$campaign->status] ?? $campaign->status }}
        </span>
        @if ($campaign->quality_score)
            <span class="ml-auto font-[Fredoka] font-semibold text-sm shrink-0
                {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                {{ $campaign->quality_score }}<span class="text-navy/30 font-normal text-[11px]">/100</span>
            </span>
        @endif
    </div>
    <p class="font-[Fredoka] font-semibold text-base leading-snug group-hover:text-copper-dark transition line-clamp-2">
        {{ $campaign->name ?? $campaign->strategy['campaign_name'] ?? $campaign->objective }}
    </p>
    <p class="text-sm text-navy/50 mt-1 line-clamp-2">{{ $campaign->objective }}</p>
    <p class="font-mono text-[10px] text-navy/35 mt-3">{{ $campaign->created_at->translatedFormat('d M Y') }}</p>
</a>
