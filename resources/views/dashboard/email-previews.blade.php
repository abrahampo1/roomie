<x-layouts.app title="Previsualizaciones">
    <header class="pb-7 sm:pb-8 mb-2">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Previsualizaciones de email</h1>
        </div>
    </header>

    <x-dashboard.nav-tabs active="email-previews" />

    @if ($campaigns->isEmpty())
        <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
            <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55 text-sm">No hay emails generados todavía.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach ($campaigns as $campaign)
                @php
                    $creative = $campaign->creative;
                    $subject = $creative['subject_line'] ?? '';
                    $headline = $creative['headline'] ?? '';
                    $bodyHtml = $creative['body_html'] ?? '';
                @endphp
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="block rounded-2xl border border-navy/15 bg-white overflow-hidden hover:border-navy/30 transition group">
                    {{-- Email preview header --}}
                    <div class="bg-navy px-5 py-4">
                        <p class="font-mono text-[10px] text-cream/40 uppercase tracking-wider mb-2">
                            {{ $campaign->created_at->translatedFormat('d M Y') }}
                        </p>
                        <p class="font-[Fredoka] font-semibold text-cream text-lg leading-snug line-clamp-2">
                            {{ $headline ?: $subject }}
                        </p>
                    </div>

                    {{-- Email preview body --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-medium text-navy/70 mb-2 truncate">
                            Asunto: {{ $subject }}
                        </p>
                        <div class="text-xs text-navy/50 leading-relaxed line-clamp-4 prose-preview">
                            {!! Str::limit(strip_tags($bodyHtml), 200) !!}
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-navy/10">
                            <span class="font-mono text-[10px] text-navy/35">
                                #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            @if ($campaign->quality_score)
                                <span class="font-[Fredoka] font-semibold text-xs
                                    {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                                    {{ $campaign->quality_score }}/100
                                </span>
                            @endif
                            <span class="text-xs text-navy/45 group-hover:text-copper transition">
                                Ver campaña &rarr;
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($campaigns->hasPages())
            <div class="mt-6 text-xs text-navy/45 font-mono">
                {{ $campaigns->links() }}
            </div>
        @endif
    @endif
</x-layouts.app>
