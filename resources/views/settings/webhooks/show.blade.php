<x-layouts.app title="{{ $webhook->name }}">
    <div class="max-w-3xl">
        <a href="{{ route('settings.webhooks.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Webhooks
        </a>

        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mt-4 mb-3">
            Ajustes · Webhook
        </p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            {{ $webhook->name }}
        </h1>
        <p class="font-mono text-sm text-navy/55 break-all mb-10">{{ $webhook->url }}</p>

        @if (session('message'))
            <div class="rounded-xl border border-navy/15 bg-sand-light/60 p-4 mb-6 text-sm text-navy/75">
                {{ session('message') }}
            </div>
        @endif

        {{-- One-shot secret display (on create + rotate) --}}
        @if ($newSecret && $newWebhookId === $webhook->id)
            <div class="rounded-2xl border-2 border-copper/60 bg-copper/5 p-6 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-copper shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L1 21h22L12 2zm0 6l7.5 13h-15L12 8zm-1 4v3h2v-3h-2zm0 4v2h2v-2h-2z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-[Fredoka] font-semibold text-lg leading-tight mb-1">Guarda el secret</p>
                        <p class="text-sm text-navy/65 leading-relaxed">
                            Necesitas este valor para verificar la firma HMAC de cada evento. Es la única vez que te lo mostraremos.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex flex-col sm:flex-row sm:items-stretch gap-2">
                    <input
                        type="text"
                        value="{{ $newSecret }}"
                        readonly
                        id="new-secret-input"
                        class="flex-1 rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-sm text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    >
                    <button
                        type="button"
                        onclick="const i=document.getElementById('new-secret-input'); i.select(); navigator.clipboard?.writeText(i.value); this.textContent='Copiado';"
                        class="bg-navy text-cream px-5 py-3 rounded-xl font-medium hover:bg-navy-light transition text-sm"
                    >
                        Copiar
                    </button>
                </div>
            </div>
        @endif

        {{-- Status + metadata --}}
        <div class="rounded-2xl border border-navy/15 bg-white p-6 mb-6">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-1">Estado</p>
                    <p class="flex items-center gap-2 text-sm">
                        <span class="w-2 h-2 rounded-full {{ $webhook->active ? 'bg-emerald-500' : 'bg-navy/25' }}"></span>
                        {{ $webhook->active ? 'Activo' : 'Inactivo' }}
                        @if ($webhook->consecutive_failures > 0)
                            <span class="text-red-700 text-xs ml-1">· {{ $webhook->consecutive_failures }} fallos seguidos</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-1">Último evento</p>
                    <p class="text-sm text-navy/70">
                        @if ($webhook->last_triggered_at)
                            {{ $webhook->last_triggered_at->translatedFormat('d M Y, H:i') }}
                            <span class="font-mono text-xs text-navy/45">· HTTP {{ $webhook->last_status_code ?? '—' }}</span>
                        @else
                            Nunca
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-5 border-t border-navy/10 pt-5">
                <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-2">Eventos suscritos</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($webhook->events as $event)
                        <span class="font-mono text-[11px] text-navy/70 bg-navy/5 px-2 py-1 rounded">{{ $event }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3 mb-10">
            <form method="POST" action="{{ route('settings.webhooks.test', $webhook) }}">
                @csrf
                <button type="submit"
                        class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                    Enviar test
                </button>
            </form>
            <form method="POST" action="{{ route('settings.webhooks.rotate-secret', $webhook) }}"
                  onsubmit="return confirm('¿Rotar el secret? Las integraciones con el secret anterior dejarán de verificar.');">
                @csrf
                <button type="submit"
                        class="border border-navy/20 text-navy/70 px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy/[0.03] transition">
                    Rotar secret
                </button>
            </form>
            <form method="POST" action="{{ route('settings.webhooks.destroy', $webhook) }}"
                  onsubmit="return confirm('¿Borrar este webhook? Esta acción no se puede deshacer.');">
                @csrf
                <button type="submit"
                        class="border border-navy/20 text-navy/70 px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy/[0.03] hover:text-red-700 hover:border-red-200 transition">
                    Eliminar
                </button>
            </form>
        </div>

        {{-- Recent deliveries --}}
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-4">
                Entregas recientes
            </p>
            @if ($deliveries->isEmpty())
                <div class="rounded-xl border border-dashed border-navy/20 p-8 text-center">
                    <p class="text-navy/55 text-sm">Todavía no hay entregas. Usa "Enviar test" para disparar una.</p>
                </div>
            @else
                <div class="rounded-2xl border border-navy/15 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-sand-light/40">
                            <tr>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Evento</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Status</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Intento</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Cuándo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr class="border-t border-navy/10">
                                    <td class="py-3 px-4 font-mono text-xs text-navy/75">{{ $delivery->event_type }}</td>
                                    <td class="py-3 px-4 font-mono text-xs">
                                        @if ($delivery->status_code && $delivery->status_code >= 200 && $delivery->status_code < 300)
                                            <span class="text-emerald-700">{{ $delivery->status_code }}</span>
                                        @elseif ($delivery->status_code)
                                            <span class="text-red-700">{{ $delivery->status_code }}</span>
                                        @else
                                            <span class="text-navy/40">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-navy/60">{{ $delivery->attempt }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-navy/60">
                                        {{ $delivery->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
