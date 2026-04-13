<x-layouts.app title="Historial de envío">
    <header class="pb-7 sm:pb-8 mb-2">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Panel de control</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Historial de envío</h1>
        </div>
    </header>

    <x-dashboard.nav-tabs active="send-history" />

    @if ($recipients->isEmpty())
        <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
            <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55 text-sm">No hay envíos registrados todavía.</p>
        </div>
    @else
        {{-- Header row --}}
        <div class="hidden sm:grid grid-cols-12 gap-3 pb-2 mb-1 border-b border-navy/15 text-[10px] font-mono text-navy/40 uppercase tracking-wider">
            <span class="col-span-3">Destinatario</span>
            <span class="col-span-3">Campaña</span>
            <span class="col-span-2">Estado</span>
            <span class="col-span-2">Opens / Clicks</span>
            <span class="col-span-2 text-right">Último envío</span>
        </div>

        <div class="divide-y divide-navy/10">
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
                    $statusColor = match ($r->status) {
                        'converted' => 'text-emerald-700',
                        'unsubscribed' => 'text-amber-700',
                        'bounced', 'failed' => 'text-red-700',
                        default => 'text-navy/55',
                    };
                @endphp
                <div class="grid grid-cols-12 gap-3 py-3 items-center text-sm">
                    <div class="col-span-6 sm:col-span-3">
                        <p class="font-mono text-xs text-navy/70 truncate">{{ $r->email }}</p>
                        @if ($r->first_name)
                            <p class="text-[11px] text-navy/45 truncate">{{ $r->first_name }}</p>
                        @endif
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <p class="text-xs text-navy/60 truncate">{{ $r->campaign_name ?? Str::limit($r->campaign_objective, 40) }}</p>
                    </div>
                    <span class="col-span-4 sm:col-span-2 text-xs {{ $statusColor }}">{{ $statusLabel }}</span>
                    <span class="col-span-4 sm:col-span-2 text-xs text-navy/45 font-mono">
                        {{ $r->opens_count }}<span class="text-navy/30"> / {{ $r->clicks_count }}</span>
                    </span>
                    <span class="col-span-4 sm:col-span-2 text-xs text-navy/40 font-mono text-right">
                        {{ $r->last_sent_at ? \Carbon\Carbon::parse($r->last_sent_at)->translatedFormat('d M H:i') : '—' }}
                    </span>
                </div>
            @endforeach
        </div>

        @if ($recipients->hasPages())
            <div class="mt-6 text-xs text-navy/45 font-mono">
                {{ $recipients->links() }}
            </div>
        @endif
    @endif
</x-layouts.app>
