<x-layouts.dashboard title="API y Webhooks" active="api">
    <div class="max-w-3xl">
        <a href="{{ route('home') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Inicio
        </a>

        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mt-4 mb-3">
            Ajustes · API
        </p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            Acceso al API
        </h1>
        <p class="text-navy/60 leading-relaxed mb-10 max-w-xl">
            Un token para autenticar tus requests y webhooks para recibir eventos en tiempo real.
            <a href="{{ route('docs') }}" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">Lee la documentación completa</a>.
        </p>

        @if (session('message'))
            <div class="rounded-xl border border-navy/15 bg-sand-light/60 p-4 mb-6 text-sm text-navy/75">
                {{ session('message') }}
            </div>
        @endif

        {{-- ═══ TOKEN ═══ --}}
        <section class="mb-16">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-4">
                Token
            </p>

            @if ($newToken)
                {{-- State B: just generated — show it once with a big warning. --}}
                <div class="rounded-2xl border-2 border-copper/60 bg-copper/5 p-6 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-copper shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L1 21h22L12 2zm0 6l7.5 13h-15L12 8zm-1 4v3h2v-3h-2zm0 4v2h2v-2h-2z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="font-[Fredoka] font-semibold text-lg leading-tight mb-1">Guárdalo ahora</p>
                            <p class="text-sm text-navy/65 leading-relaxed">
                                Esta es la única vez que te mostraremos este token. Si lo pierdes, tendrás que generar uno nuevo y actualizar todas las integraciones que lo estén usando.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col sm:flex-row sm:items-stretch gap-2">
                        <input
                            type="text"
                            value="{{ $newToken }}"
                            readonly
                            id="new-token-input"
                            class="flex-1 rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-sm text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                        >
                        <button
                            type="button"
                            onclick="const i=document.getElementById('new-token-input'); i.select(); navigator.clipboard?.writeText(i.value); this.textContent='Copiado';"
                            class="bg-navy text-cream px-5 py-3 rounded-xl font-medium hover:bg-navy-light transition text-sm"
                        >
                            Copiar
                        </button>
                    </div>
                </div>
            @endif

            @if ($user->api_token)
                {{-- State C: token exists (hash in db, plain gone) --}}
                <div class="rounded-2xl border border-navy/15 bg-white p-6 mb-6">
                    <div class="flex items-start gap-3 mb-5">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-[Fredoka] font-semibold text-lg leading-tight">Token activo</p>
                            <p class="text-sm text-navy/55 font-mono mt-1">
                                @if ($user->api_token_created_at)
                                    Creado el {{ $user->api_token_created_at->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('settings.api-token.generate') }}">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                                Regenerar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('settings.api-token.revoke') }}" onsubmit="return confirm('¿Seguro? Cualquier integración que lo esté usando dejará de funcionar inmediatamente.');">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto border border-navy/20 text-navy/70 px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy/[0.03] hover:text-red-700 hover:border-red-200 transition">
                                Revocar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- State A: no token yet --}}
                <div class="rounded-2xl border border-dashed border-navy/20 p-8 text-center mb-6">
                    <svg class="w-6 h-6 text-navy/25 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                    <p class="text-navy/55 leading-relaxed mb-5 max-w-md mx-auto">
                        Todavía no has generado un token. Crea uno para empezar a usar el API desde tus integraciones.
                    </p>
                    <form method="POST" action="{{ route('settings.api-token.generate') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3 rounded-full font-medium hover:bg-navy-light transition">
                            Generar token
                            <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        </button>
                    </form>
                </div>
            @endif

            <div class="pt-6 border-t border-navy/10">
                <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/40 mb-4">
                    Ejemplo rápido
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto leading-relaxed">curl {{ url('/api/v1/campaigns') }} \
  -H "Authorization: Bearer {{ $newToken ?? 'TU_TOKEN_AQUI' }}" \
  -H "Accept: application/json"</pre>
            </div>
        </section>

        {{-- ═══ WEBHOOKS ═══ --}}
        <section id="webhooks" class="mb-16 pt-10 border-t border-navy/10">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-4">
                Webhooks
            </p>
            <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight leading-[1.05] mb-3">
                Suscripciones a eventos
            </h2>
            <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                Apunta un endpoint HTTPS a los eventos que te interesen y recíbelos firmados con HMAC. Cada POST lleva una cabecera
                <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">X-Roomie-Signature</code>
                — <a href="{{ route('docs') }}#webhooks-signing" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">cómo verificarla</a>.
            </p>

            {{-- Secret flash (after create / rotate) --}}
            @if ($newWebhookSecret)
                <div class="rounded-2xl border-2 border-copper/60 bg-copper/5 p-6 mb-8">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-copper shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L1 21h22L12 2zm0 6l7.5 13h-15L12 8zm-1 4v3h2v-3h-2zm0 4v2h2v-2h-2z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="font-[Fredoka] font-semibold text-lg leading-tight mb-1">Guarda el secret</p>
                            <p class="text-sm text-navy/65 leading-relaxed">
                                Necesitas este valor para verificar la firma de cada evento. Es la única vez que te lo mostraremos.
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col sm:flex-row sm:items-stretch gap-2">
                        <input
                            type="text"
                            value="{{ $newWebhookSecret }}"
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

            {{-- Existing webhooks list --}}
            @if ($webhooks->isEmpty())
                <div class="rounded-2xl border border-dashed border-navy/20 p-8 text-center mb-8">
                    <p class="text-navy/55 leading-relaxed max-w-md mx-auto">
                        Todavía no tienes webhooks. Usa el formulario de abajo para crear el primero.
                    </p>
                </div>
            @else
                <div class="space-y-3 mb-8">
                    @foreach ($webhooks as $webhook)
                        <div class="rounded-2xl border border-navy/15 bg-white p-5">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-2 h-2 rounded-full mt-2 shrink-0 {{ $webhook->active ? 'bg-emerald-500' : 'bg-navy/25' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-4 mb-1">
                                        <p class="font-[Fredoka] font-semibold text-lg leading-tight truncate">
                                            {{ $webhook->name }}
                                        </p>
                                        <span class="font-mono text-[10px] uppercase tracking-widest text-navy/40 shrink-0">
                                            @if (! $webhook->active)
                                                Inactivo
                                            @elseif ($webhook->consecutive_failures > 0)
                                                {{ $webhook->consecutive_failures }} fallos
                                            @else
                                                Activo
                                            @endif
                                        </span>
                                    </div>
                                    <p class="font-mono text-xs text-navy/55 break-all mb-3">{{ $webhook->url }}</p>
                                    <div class="flex flex-wrap gap-1.5 mb-1">
                                        @foreach ($webhook->events as $event)
                                            <span class="font-mono text-[10px] text-navy/60 bg-navy/5 px-2 py-0.5 rounded">{{ $event }}</span>
                                        @endforeach
                                    </div>
                                    @if ($webhook->last_triggered_at)
                                        <p class="font-mono text-[10px] text-navy/40 mt-2">
                                            Último evento {{ $webhook->last_triggered_at->diffForHumans() }}
                                            · HTTP {{ $webhook->last_status_code ?? '—' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 pt-4 border-t border-navy/10">
                                <form method="POST" action="{{ route('settings.webhooks.test', $webhook) }}">
                                    @csrf
                                    <button type="submit" class="bg-navy text-cream px-4 py-1.5 rounded-full text-xs font-medium hover:bg-navy-light transition">
                                        Enviar test
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('settings.webhooks.update', $webhook) }}">
                                    @csrf
                                    <input type="hidden" name="active" value="{{ $webhook->active ? '0' : '1' }}">
                                    <button type="submit" class="border border-navy/20 text-navy/70 px-4 py-1.5 rounded-full text-xs font-medium hover:bg-navy/[0.03] transition">
                                        {{ $webhook->active ? 'Desactivar' : 'Reactivar' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('settings.webhooks.rotate-secret', $webhook) }}"
                                      onsubmit="return confirm('¿Rotar el secret? Las integraciones con el valor anterior dejarán de verificar.');">
                                    @csrf
                                    <button type="submit" class="border border-navy/20 text-navy/70 px-4 py-1.5 rounded-full text-xs font-medium hover:bg-navy/[0.03] transition">
                                        Rotar secret
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('settings.webhooks.destroy', $webhook) }}"
                                      onsubmit="return confirm('¿Borrar este webhook?');">
                                    @csrf
                                    <button type="submit" class="border border-navy/20 text-navy/70 px-4 py-1.5 rounded-full text-xs font-medium hover:bg-navy/[0.03] hover:text-red-700 hover:border-red-200 transition">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Create form --}}
            <details class="rounded-2xl border border-navy/15 bg-white overflow-hidden" {{ (isset($errors) && $errors->any()) ? 'open' : '' }}>
                <summary class="p-5 cursor-pointer font-[Fredoka] font-semibold text-base hover:bg-navy/[0.02] transition select-none">
                    + Nuevo webhook
                </summary>
                <form method="POST" action="{{ route('settings.webhooks.store') }}" class="p-5 pt-0 space-y-5 border-t border-navy/10">
                    @csrf

                    <div>
                        <label for="webhook-name" class="block font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">
                            Nombre
                        </label>
                        <input
                            type="text"
                            id="webhook-name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Slack de growth"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            required
                        >
                        @error('name')
                            <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="webhook-url" class="block font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">
                            URL (https)
                        </label>
                        <input
                            type="url"
                            id="webhook-url"
                            name="url"
                            value="{{ old('url') }}"
                            placeholder="https://hooks.mi-empresa.com/roomie"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base font-mono text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                            required
                        >
                        @error('url')
                            <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="block font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-3">
                            Eventos
                        </p>
                        <label class="flex items-center gap-3 mb-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="events[]"
                                value="*"
                                class="w-4 h-4 rounded border-navy/30 text-navy focus:ring-navy/30"
                                {{ in_array('*', old('events', [])) ? 'checked' : '' }}
                            >
                            <span class="font-[Fredoka] font-medium text-navy">Todos los eventos</span>
                        </label>

                        <div class="border-t border-navy/10 pt-4 mt-4 grid sm:grid-cols-2 gap-4">
                            <div>
                                <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-2">Campaign lifecycle</p>
                                <div class="space-y-1.5">
                                    @foreach ($campaignEvents as $event)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="events[]"
                                                value="{{ $event }}"
                                                class="w-4 h-4 rounded border-navy/30 text-navy focus:ring-navy/30"
                                                {{ in_array($event, old('events', [])) ? 'checked' : '' }}
                                            >
                                            <code class="font-mono text-[11px] text-navy/75">{{ $event }}</code>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-2">Recipient lifecycle</p>
                                <div class="space-y-1.5">
                                    @foreach ($recipientEvents as $event)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="events[]"
                                                value="{{ $event }}"
                                                class="w-4 h-4 rounded border-navy/30 text-navy focus:ring-navy/30"
                                                {{ in_array($event, old('events', [])) ? 'checked' : '' }}
                                            >
                                            <code class="font-mono text-[11px] text-navy/75">{{ $event }}</code>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('events')
                            <p class="text-sm text-red-700 mt-3">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-navy text-cream px-6 py-3 rounded-full text-sm font-medium hover:bg-navy-light transition">
                            Crear webhook
                        </button>
                    </div>
                </form>
            </details>
        </section>

        {{-- ═══ DELIVERY HISTORY ═══ --}}
        <section id="deliveries" class="mb-16 pt-10 border-t border-navy/10">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-4">
                Historial
            </p>
            <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight leading-[1.05] mb-3">
                Entregas recientes
            </h2>
            <p class="text-navy/60 leading-relaxed mb-8 max-w-xl">
                Últimos 25 envíos a cualquiera de tus webhooks. Las entregas se conservan 7 días.
            </p>

            @if ($deliveries->isEmpty())
                <div class="rounded-xl border border-dashed border-navy/20 p-8 text-center">
                    <p class="text-navy/55 text-sm">
                        @if ($webhooks->isEmpty())
                            Crea un webhook para empezar a ver entregas aquí.
                        @else
                            Todavía no hay entregas. Usa "Enviar test" en un webhook para disparar una.
                        @endif
                    </p>
                </div>
            @else
                <div class="rounded-2xl border border-navy/15 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-sand-light/40">
                            <tr>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Evento</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Webhook</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Status</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40 hidden sm:table-cell">Intento</th>
                                <th class="text-left py-3 px-4 font-mono text-[10px] uppercase tracking-widest text-navy/40">Cuándo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr class="border-t border-navy/10">
                                    <td class="py-3 px-4 font-mono text-xs text-navy/75">{{ $delivery->event_type }}</td>
                                    <td class="py-3 px-4 text-xs text-navy/60 truncate max-w-[12rem]">{{ $delivery->webhook?->name ?? '—' }}</td>
                                    <td class="py-3 px-4 font-mono text-xs">
                                        @if ($delivery->status_code && $delivery->status_code >= 200 && $delivery->status_code < 300)
                                            <span class="text-emerald-700">{{ $delivery->status_code }}</span>
                                        @elseif ($delivery->status_code)
                                            <span class="text-red-700">{{ $delivery->status_code }}</span>
                                        @else
                                            <span class="text-navy/40">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-navy/60 hidden sm:table-cell">{{ $delivery->attempt }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-navy/60">{{ $delivery->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.dashboard>
