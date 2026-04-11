<div id="campaign-stats" data-refresh-url="{{ route('campaigns.stats', $campaign) }}">
    <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5 flex items-center gap-2">
        Estadísticas
        <button type="button" data-stats-refresh class="text-navy/40 hover:text-navy transition underline underline-offset-4 decoration-navy/20">
            refrescar
        </button>
    </p>

    @if ($campaign->followups_enabled && $campaign->api_key_retained_for_followups)
        <div class="mb-8 flex items-start gap-3 p-4 rounded-xl bg-copper/5 border border-copper/30">
            <svg class="w-4 h-4 text-copper mt-0.5 shrink-0" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-[Fredoka] font-semibold">Follow-ups activos</p>
                <p class="text-xs text-navy/55 font-mono mt-1">
                    máx {{ $campaign->followup_max_attempts }} intentos · cooldown {{ $campaign->followup_cooldown_hours }}h
                    @if ($campaign->api_key_retention_expires_at)
                        · expira {{ $campaign->api_key_retention_expires_at->translatedFormat('d M') }}
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('campaigns.stop-followups', $campaign) }}">
                @csrf
                <button type="submit" class="text-xs text-navy/55 hover:text-red-700 transition underline underline-offset-4 decoration-navy/20 hover:decoration-red-700">
                    Detener secuencia
                </button>
            </form>
        </div>
    @endif

    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-6 mb-10">
        <div>
            <dd class="font-[Fredoka] font-semibold text-3xl sm:text-4xl leading-none">{{ $stats['sent'] }}</dd>
            <dt class="text-xs text-navy/45 mt-1.5">Enviados</dt>
        </div>
        <div>
            <dd class="font-[Fredoka] font-semibold text-3xl sm:text-4xl leading-none">{{ $stats['open_rate'] }}<span class="text-base text-navy/35">%</span></dd>
            <dt class="text-xs text-navy/45 mt-1.5">Abiertos ({{ $stats['opened'] }})</dt>
        </div>
        <div>
            <dd class="font-[Fredoka] font-semibold text-3xl sm:text-4xl leading-none text-copper">{{ $stats['click_rate'] }}<span class="text-base text-navy/35">%</span></dd>
            <dt class="text-xs text-navy/45 mt-1.5">Clicks ({{ $stats['clicked'] }})</dt>
        </div>
        <div>
            <dd class="font-[Fredoka] font-semibold text-3xl sm:text-4xl leading-none text-emerald-700">{{ $stats['conversion_rate'] }}<span class="text-base text-navy/35">%</span></dd>
            <dt class="text-xs text-navy/45 mt-1.5">Conversión ({{ $stats['converted'] }})</dt>
        </div>
    </dl>

    @if ($stats['bounced'] > 0 || $stats['failed'] > 0 || $stats['unsubscribed'] > 0)
        <p class="text-xs text-navy/50 font-mono mb-8">
            @if ($stats['bounced'] > 0) {{ $stats['bounced'] }} rebotes @endif
            @if ($stats['failed'] > 0) · {{ $stats['failed'] }} fallos @endif
            @if ($stats['unsubscribed'] > 0) · {{ $stats['unsubscribed'] }} bajas @endif
        </p>
    @endif

    @if ($recipients->isNotEmpty())
        <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-4">
            Destinatarios
        </p>
        <div class="border-t border-navy/10">
            @foreach ($recipients as $r)
                <div class="grid grid-cols-12 gap-3 py-3 border-b border-navy/10 items-center text-sm">
                    <span class="col-span-6 sm:col-span-4 font-mono text-xs text-navy/70 truncate">{{ $r->email }}</span>
                    <span class="col-span-3 sm:col-span-2 text-xs {{ match ($r->status) {
                        'converted' => 'text-emerald-700',
                        'unsubscribed' => 'text-amber-700',
                        'bounced', 'failed' => 'text-red-700',
                        default => 'text-navy/55',
                    } }}">
                        {{ match ($r->status) {
                            'queued' => 'En cola',
                            'sending' => 'Enviando',
                            'sent' => 'Enviado',
                            'bounced' => 'Rebote',
                            'failed' => 'Fallido',
                            'unsubscribed' => 'Baja',
                            'converted' => 'Convertido',
                            default => $r->status,
                        } }}
                    </span>
                    <span class="hidden sm:inline col-span-2 text-xs text-navy/45 font-mono">
                        {{ $r->opens_count }}<span class="text-navy/30">/{{ $r->clicks_count }}</span>
                    </span>
                    <span class="col-span-2 text-xs text-navy/40 font-mono text-right">
                        {{ $r->attempts_sent > 0 ? 'intento '.$r->attempts_sent : '—' }}
                    </span>
                    <form method="POST" action="{{ route('campaigns.recipients.toggle-conversion', ['campaign' => $campaign, 'recipient' => $r]) }}" class="col-span-3 sm:col-span-2 text-right">
                        @csrf
                        <button type="submit" class="text-[11px] text-navy/45 hover:text-navy transition underline underline-offset-4 decoration-navy/20">
                            {{ $r->isConverted() ? 'Deshacer' : 'Convertido' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        @if ($recipients->hasPages())
            <div class="mt-4 text-xs text-navy/45 font-mono">
                {{ $recipients->links() }}
            </div>
        @endif
    @endif
</div>

<script>
    (function () {
        const container = document.getElementById('campaign-stats');
        if (!container || container.dataset.wired === '1') return;
        container.dataset.wired = '1';

        const refreshBtn = container.querySelector('[data-stats-refresh]');
        const url = container.dataset.refreshUrl;

        async function refresh() {
            try {
                const res = await fetch(url, {
                    headers: { 'Accept': 'text/html' },
                });
                if (!res.ok) return;
                const html = await res.text();
                container.outerHTML = html;
            } catch (e) { /* noop */ }
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', refresh);
        }
    })();
</script>
