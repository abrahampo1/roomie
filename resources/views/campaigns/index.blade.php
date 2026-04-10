<x-layouts.app title="Campañas">
    {{-- ═══════════════════════ EDITORIAL HEADER ═══════════════════════ --}}
    <section class="relative mb-12 pb-8 border-b-2 border-navy">
        <svg class="absolute top-2 right-0 w-10 h-10 text-copper animate-[spin_16s_linear_infinite]" viewBox="0 0 24 24">
            <use href="#roomie-sparkle"/>
        </svg>

        <p class="text-xs uppercase tracking-[0.3em] text-copper font-bold mb-3">/ archivo</p>
        <div class="flex items-end justify-between gap-6 flex-wrap">
            <h1 class="font-[Fredoka] font-bold leading-[0.85] tracking-tighter text-[clamp(3rem,9vw,7rem)]">
                Camp<span class="text-outline-thick">añas</span>
            </h1>
            <div class="flex items-center gap-8">
                <div class="text-right">
                    <p class="text-5xl font-[Fredoka] font-bold leading-none">{{ str_pad($campaigns->count(), 2, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-navy/50 mt-1">Total generadas</p>
                </div>
                <a href="{{ route('campaigns.create') }}"
                   class="inline-flex items-center gap-2 bg-navy text-cream px-5 py-3 rounded-xl font-semibold border-2 border-navy shadow-[4px_4px_0_0_#c8956c] hover:shadow-[2px_2px_0_0_#c8956c] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                    <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    Nueva
                </a>
            </div>
        </div>
    </section>

    @if ($campaigns->isEmpty())
        <div class="relative text-center py-24 bg-white rounded-3xl border-2 border-navy overflow-hidden shadow-[6px_6px_0_0_#1a1a2e]">
            <div class="absolute inset-0 opacity-[0.04] text-navy">
                <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-stars)"/></svg>
            </div>
            <div class="relative">
                <svg class="w-16 h-16 text-navy/20 mx-auto mb-6 animate-[float_7s_ease-in-out_infinite]" viewBox="0 0 24 24">
                    <use href="#roomie-sparkle"/>
                </svg>
                <p class="text-2xl font-[Fredoka] font-bold mb-2">Nada aún.</p>
                <p class="text-navy/50 mb-6">Ninguna campaña ha pasado por el pipeline.</p>
                <a href="{{ route('campaigns.create') }}"
                   class="inline-flex items-center gap-2 bg-navy text-cream px-5 py-2.5 rounded-xl font-semibold border-2 border-navy shadow-[4px_4px_0_0_#c8956c] hover:shadow-[2px_2px_0_0_#c8956c] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                    Crear la primera
                    <svg class="w-3.5 h-3.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </a>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($campaigns as $campaign)
                @php
                    $statusColors = [
                        'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500'],
                        'processing' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500 animate-pulse'],
                        'pending' => ['bg' => 'bg-navy/10', 'text' => 'text-navy/60', 'dot' => 'bg-navy/40 animate-pulse'],
                        'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'dot' => 'bg-red-500'],
                    ];
                    $c = $statusColors[$campaign->status] ?? $statusColors['pending'];
                @endphp
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="group relative flex items-stretch bg-white rounded-2xl border-2 border-navy overflow-hidden hover:shadow-[6px_6px_0_0_#1a1a2e] hover:-translate-x-[3px] hover:-translate-y-[3px] transition-all">
                    {{-- Index strip --}}
                    <div class="flex items-center justify-center w-16 bg-navy text-cream border-r-2 border-navy shrink-0 relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.15] text-cream">
                            <svg width="100%" height="100%"><rect width="100%" height="100%" fill="url(#pat-diag)"/></svg>
                        </div>
                        <span class="relative font-[Fredoka] font-bold text-lg">#{{ str_pad($campaign->id, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="flex-1 min-w-0 p-5 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-[Fredoka] font-semibold text-lg truncate group-hover:text-copper-dark transition-colors">
                                {{ $campaign->strategy['campaign_name'] ?? $campaign->objective }}
                            </p>
                            <p class="text-sm text-navy/50 truncate mt-0.5">{{ $campaign->objective }}</p>
                        </div>

                        <div class="flex items-center gap-4 shrink-0">
                            @if ($campaign->quality_score)
                                <div class="text-center">
                                    <p class="text-xl font-[Fredoka] font-bold leading-none
                                        {{ $campaign->quality_score >= 80 ? 'text-emerald-600' : ($campaign->quality_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $campaign->quality_score }}
                                    </p>
                                    <p class="text-[9px] uppercase tracking-wider text-navy/40 mt-0.5">Score</p>
                                </div>
                            @endif

                            <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-widest font-bold px-3 py-1.5 rounded-full {{ $c['bg'] }} {{ $c['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                                {{ $campaign->status }}
                            </span>

                            <svg class="w-4 h-4 text-navy/30 group-hover:text-copper group-hover:rotate-45 transition-all" viewBox="0 0 24 24">
                                <use href="#roomie-sparkle"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
