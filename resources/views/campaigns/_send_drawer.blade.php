@php
    $allowRealSends = (bool) config('services.roomie.allow_real_sends', false);
    $recipientCount = $campaign->recipients()->count();
    $alreadySent = $campaign->send_enabled;
    $providerLabel = $campaign->api_provider
        ? \App\Services\LLM\LlmClientFactory::label($campaign->api_provider)
        : null;
    $providerDisplay = $providerLabel;
    if ($providerLabel && $campaign->api_provider === 'custom' && $campaign->api_model) {
        $providerDisplay .= ' · '.$campaign->api_model;
    }
    $keyPlaceholder = match ($campaign->api_provider) {
        'anthropic' => 'sk-ant-...',
        'google' => 'AIza...',
        'openai' => 'sk-proj-...',
        'deepseek' => 'sk-...',
        default => 'sk-...',
    };
@endphp

<section class="mt-20 sm:mt-24 pt-12 border-t border-navy/15">
    <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-5">
        Envío
    </p>

    @if ($alreadySent)
        <div class="mb-10 flex items-start gap-3">
            <svg class="w-4 h-4 text-emerald-600 mt-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            <div>
                <p class="font-[Fredoka] font-semibold text-lg leading-tight">
                    Campaña enviada
                </p>
                <p class="text-sm text-navy/55 mt-1">
                    {{ $recipientCount }} {{ $recipientCount === 1 ? 'destinatario' : 'destinatarios' }}
                    @if ($campaign->sent_at)
                        · {{ $campaign->sent_at->translatedFormat('d M Y H:i') }}
                    @endif
                </p>
            </div>
        </div>

        @if (! empty($stats) && ! empty($recipients))
            @include('campaigns._stats_section', ['campaign' => $campaign, 'stats' => $stats, 'recipients' => $recipients])
        @endif
    @else
        <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-tight mb-3 max-w-2xl">
            Enviar la campaña a los clientes recomendados.
        </h2>
        <p class="text-navy/60 leading-relaxed max-w-xl mb-6">
            Seleccionaremos a los clientes que mejor encajan con la estrategia
            (mismo hotel, ADR similar, mejor score histórico) y lanzaremos el email
            generado. Por defecto enviaremos a los 50 más afines.
        </p>

        @if (! $allowRealSends)
            <div class="mb-6 inline-flex items-start gap-2.5 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5 max-w-xl">
                <svg class="w-3.5 h-3.5 text-amber-600 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L1 21h22L12 2zm0 6l7.5 13h-15L12 8zm-1 4v3h2v-3h-2zm0 4v2h2v-2h-2z"/>
                </svg>
                <div class="leading-relaxed">
                    <strong class="font-semibold">Modo demo.</strong> Los emails se escriben al log de Laravel en vez de enviarse a buzones reales. Flipa <code class="font-mono text-[11px] bg-amber-100 px-1 py-0.5 rounded">ROOMIE_ALLOW_REAL_SENDS=true</code> en el <code class="font-mono text-[11px] bg-amber-100 px-1 py-0.5 rounded">.env</code> para usar el mailer real.
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('campaigns.send', $campaign) }}" class="space-y-6">
            @csrf

            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div>
                    <label for="recipient_count" class="block text-xs text-navy/55 mb-1.5">Nº de destinatarios</label>
                    <select
                        name="recipient_count"
                        id="recipient_count"
                        class="rounded-xl border border-navy/20 bg-white px-4 py-2.5 text-base text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>
            </div>

            <details class="group border-t border-navy/10 pt-6">
                <summary class="cursor-pointer text-sm font-medium flex items-center gap-2 select-none list-none">
                    <span class="inline-block transition-transform group-open:rotate-90 text-navy/40">›</span>
                    Activar follow-ups automáticos (opcional)
                </summary>

                <div class="mt-5 space-y-5 pl-4 border-l-2 border-copper/40">
                    <p class="text-xs text-navy/55 leading-relaxed max-w-xl">
                        Si los destinatarios no reaccionan, Roomie puede generar emails de seguimiento cada vez más insistentes hasta que hagan click o se den de baja. Los follow-ups usan <strong class="text-navy">la misma IA que la campaña original</strong> — @if ($providerDisplay){{ $providerDisplay }}@else tu proveedor configurado @endif — así que necesitamos guardar tu clave API cifrada en el servidor, hasta {{ (int) config('services.roomie.followup_max_retention_days', 14) }} días o hasta que la secuencia termine, lo que ocurra antes. Puedes revocarla desde el botón "Detener secuencia".
                    </p>

                    <label class="flex items-start gap-2 text-sm cursor-pointer select-none">
                        <input type="checkbox" name="enable_followups" value="1" id="enable-followups-toggle" class="mt-1 rounded border-navy/30 text-navy focus:ring-navy/20">
                        <span class="leading-snug">
                            Autorizo a Roomie a guardar mi clave cifrada para generar follow-ups automáticos.
                        </span>
                    </label>

                    <div id="followups-config" hidden class="space-y-4">
                        <div>
                            <label for="followup_api_key" class="block text-xs text-navy/55 mb-1.5">
                                Vuelve a pegar tu API key
                                @if ($providerLabel)
                                    <span class="text-navy font-medium">de {{ $providerLabel }}</span>
                                @endif
                            </label>
                            <input
                                type="password"
                                name="followup_api_key"
                                id="followup_api_key"
                                autocomplete="off"
                                autocapitalize="none"
                                autocorrect="off"
                                spellcheck="false"
                                class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 font-mono text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                                placeholder="{{ $keyPlaceholder }}"
                            >
                            <p class="text-xs text-navy/45 mt-1.5">
                                Tiene que ser del mismo proveedor y modelo que la campaña original
                                @if ($campaign->api_provider === 'custom' && $campaign->api_base_url)
                                    ({{ parse_url($campaign->api_base_url, PHP_URL_HOST) ?? $campaign->api_base_url }})
                                @endif.
                                Se cifrará con tu APP_KEY antes de guardarse.
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label for="followup_max_attempts" class="block text-xs text-navy/55 mb-1.5">Intentos máximos</label>
                                <select name="followup_max_attempts" id="followup_max_attempts" class="w-full rounded-xl border border-navy/20 bg-white px-4 py-2.5 text-base">
                                    <option value="2">2</option>
                                    <option value="3" selected>3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="followup_cooldown_hours" class="block text-xs text-navy/55 mb-1.5">Cooldown (horas)</label>
                                <select name="followup_cooldown_hours" id="followup_cooldown_hours" class="w-full rounded-xl border border-navy/20 bg-white px-4 py-2.5 text-base">
                                    <option value="1">1 (demo)</option>
                                    <option value="24">24</option>
                                    <option value="48" selected>48</option>
                                    <option value="72">72</option>
                                    <option value="168">168 (1 semana)</option>
                                </select>
                            </div>
                        </div>

                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 leading-relaxed">
                            Cada intento sube un escalón la agresividad y la manipulación (cap en 5). Y consume tokens de tu clave — tú pagas cada generación.
                        </p>
                    </div>
                </div>
            </details>

            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition w-full sm:w-auto">
                Enviar ahora
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </button>
        </form>
    @endif
</section>

<script>
    (function () {
        const toggle = document.getElementById('enable-followups-toggle');
        const config = document.getElementById('followups-config');
        if (!toggle || !config) return;
        const apply = () => {
            config.hidden = !toggle.checked;
            const keyInput = config.querySelector('#followup_api_key');
            if (keyInput) keyInput.required = toggle.checked;
        };
        toggle.addEventListener('change', apply);
        apply();
    })();
</script>
