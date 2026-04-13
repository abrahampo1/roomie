<x-layouts.dashboard title="Historial de envío" active="send-history">
    <header class="pb-7 sm:pb-8 mb-2">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Historial de envío</h1>
        </div>
    </header>

    @if ($recipients->isEmpty())
        <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
            <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55 text-sm">No hay envíos registrados todavía.</p>
        </div>
    @else
        <div class="rounded-2xl border border-navy/10 bg-white overflow-hidden">
            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-navy/[0.02]">
                            <th class="px-5 py-3 text-[10px] font-mono font-normal uppercase tracking-[0.18em] text-navy/40">Destinatario</th>
                            <th class="px-5 py-3 text-[10px] font-mono font-normal uppercase tracking-[0.18em] text-navy/40">Campaña</th>
                            <th class="px-5 py-3 text-[10px] font-mono font-normal uppercase tracking-[0.18em] text-navy/40">Estado</th>
                            <th class="px-5 py-3 text-[10px] font-mono font-normal uppercase tracking-[0.18em] text-navy/40">Opens / Clicks</th>
                            <th class="px-5 py-3 text-[10px] font-mono font-normal uppercase tracking-[0.18em] text-navy/40 text-right">Último envío</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/[0.06]">
                        @foreach ($recipients as $r)
                            @php
                                $statusLabel = match ($r->status) {
                                    'queued' => 'En cola',
                                    'sending' => 'Enviando',
                                    'sent' => 'Enviado',
                                    'bounced' => 'Rebote',
                                    'failed' => 'Fallido',
                                    'unsubscribed' => 'Baja',
                                    'converted' => 'Convertido',
                                    default => $r->status,
                                };

                                $badgeBg = match ($r->status) {
                                    'converted' => 'bg-emerald-50 text-emerald-700',
                                    'unsubscribed' => 'bg-amber-50 text-amber-700',
                                    'bounced', 'failed' => 'bg-red-50 text-red-700',
                                    default => 'bg-navy/[0.04] text-navy/55',
                                };
                            @endphp
                            <tr class="hover:bg-navy/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="text-sm text-navy/80 truncate max-w-[200px]">{{ $r->email }}</p>
                                    @if ($r->first_name)
                                        <p class="text-[11px] text-navy/40 mt-0.5">{{ $r->first_name }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-sm text-navy/60 truncate max-w-[220px]">{{ $r->campaign_name ?? Str::limit($r->campaign_objective, 40) }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-medium {{ $badgeBg }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-sm font-mono text-navy/60 tabular-nums">
                                        {{ $r->opens_count }}<span class="text-navy/25 mx-0.5">/</span>{{ $r->clicks_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-xs font-mono text-navy/40">
                                        {{ $r->last_sent_at ? \Carbon\Carbon::parse($r->last_sent_at)->translatedFormat('d M H:i') : '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($recipients->hasPages())
            <div class="mt-6 text-xs text-navy/45 font-mono">
                {{ $recipients->links() }}
            </div>
        @endif
    @endif
</x-layouts.dashboard>
