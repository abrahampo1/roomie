<x-layouts.dashboard title="Previsualizaciones" active="email-previews">
    <header class="pb-7 sm:pb-8 mb-2">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Previsualizaciones de email</h1>
        </div>
    </header>

    @if ($campaigns->isEmpty())
        <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
            <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55 text-sm">No hay emails generados todavía.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($campaigns as $campaign)
                @php
                    $creative = $campaign->creative;
                    $subject = $creative['subject_line'] ?? '';
                    $headline = $creative['headline'] ?? '';
                    $bodyHtml = $creative['body_html'] ?? '';
                    $previewText = $creative['preview_text'] ?? '';
                @endphp
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="flex flex-col rounded-2xl border border-navy/10 bg-white overflow-hidden hover:border-navy/25 transition group">
                    {{-- Card header --}}
                    <div class="bg-navy px-5 py-4">
                        <p class="font-mono text-[10px] text-cream/35 uppercase tracking-[0.18em] mb-2">
                            {{ $campaign->created_at->translatedFormat('d M Y') }}
                        </p>
                        <p class="font-[Fredoka] font-semibold text-cream text-lg leading-snug line-clamp-2">
                            {{ $headline ?: $subject }}
                        </p>
                    </div>

                    {{-- Card body --}}
                    <div class="flex-1 px-5 py-4">
                        <p class="text-xs font-medium text-navy/70 mb-1.5 truncate">
                            <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40">Asunto</span>
                        </p>
                        <p class="text-sm text-navy/65 mb-3 truncate">{{ $subject }}</p>

                        <div class="text-xs text-navy/45 leading-relaxed line-clamp-4">
                            {!! Str::limit(strip_tags($bodyHtml), 200) !!}
                        </div>
                    </div>

                    {{-- Card footer --}}
                    <div class="px-5 py-3.5 border-t border-navy/[0.06] flex items-center justify-between">
                        <span class="font-mono text-[10px] text-navy/30 tabular-nums">
                            #{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                        @if ($campaign->quality_score)
                            <span class="font-[Fredoka] font-semibold text-xs tabular-nums
                                {{ $campaign->quality_score >= 80 ? 'text-emerald-700' : ($campaign->quality_score >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                                {{ $campaign->quality_score }}/100
                            </span>
                        @endif
                        <span class="text-xs text-navy/40 group-hover:text-copper transition">
                            Ver campaña &rarr;
                        </span>
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
</x-layouts.dashboard>
