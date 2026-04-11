@php
    $allowRealSends = (bool) config('services.roomie.allow_real_sends', false);
    $recipientCount = $campaign->recipients()->count();
    $alreadySent = $campaign->send_enabled;
    $hasStoredKey = $campaign->getRawOriginal('api_key') !== null;
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
    $max = (int) ($maxRecipients ?? 0);
    $retentionDays = (int) config('services.roomie.followup_max_retention_days', 14);
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
            @include('campaigns._stats_section', [
                'campaign' => $campaign,
                'stats' => $stats,
                'funnel' => $funnel,
                'timeSeries' => $timeSeries,
                'countryBreakdown' => $countryBreakdown,
                'segmentBreakdown' => $segmentBreakdown,
                'followupPerformance' => $followupPerformance,
                'recipients' => $recipients,
            ])
        @endif
    @else
        <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-tight mb-3 max-w-2xl">
            Enviar la campaña a los clientes mejor encajados.
        </h2>
        <p class="text-navy/60 leading-relaxed max-w-xl mb-8">
            Los destinatarios se seleccionan automáticamente a partir de la estrategia: mismo hotel, ADR compatible, mejor score histórico. Solo decides cuántos.
        </p>

        @if (! $allowRealSends)
            <div class="mb-8 inline-flex items-start gap-2.5 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5 max-w-xl">
                <svg class="w-3.5 h-3.5 text-amber-600 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L1 21h22L12 2zm0 6l7.5 13h-15L12 8zm-1 4v3h2v-3h-2zm0 4v2h2v-2h-2z"/>
                </svg>
                <div class="leading-relaxed">
                    <strong class="font-semibold">Modo demo.</strong> Los emails se escriben al log de Laravel en vez de enviarse. Flipa <code class="font-mono text-[11px] bg-amber-100 px-1 py-0.5 rounded">ROOMIE_ALLOW_REAL_SENDS=true</code> en <code class="font-mono text-[11px] bg-amber-100 px-1 py-0.5 rounded">.env</code> para usar el mailer real.
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('campaigns.send', $campaign) }}" class="space-y-10">
            @csrf

            {{-- ═══ Recipient count ═══ --}}
            <div>
                <p class="text-sm font-medium mb-4">Cuántos destinatarios</p>

                <div id="recipient-modes" class="flex flex-wrap gap-2 mb-4">
                    @foreach ([
                        '50' => '50',
                        '100' => '100',
                        '200' => '200',
                        'custom' => 'Personalizado',
                        'all' => 'Todos',
                    ] as $value => $label)
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="recipient_mode"
                                value="{{ $value }}"
                                class="peer sr-only"
                                {{ $value === '50' ? 'checked' : '' }}
                                data-value="{{ $value }}"
                            >
                            <span class="block px-4 py-2 text-sm font-medium rounded-xl border border-navy/15 bg-white text-navy/65 peer-checked:bg-navy peer-checked:text-cream peer-checked:border-navy transition-colors">
                                {{ $label }}
                                @if ($value === 'all')
                                    <span class="ml-1 text-[11px] font-mono opacity-70">({{ $max }})</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <div id="custom-count-wrapper" hidden class="mb-4">
                    <label for="recipient_count_custom" class="sr-only">Número de destinatarios</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="number"
                            name="recipient_count_custom"
                            id="recipient_count_custom"
                            min="1"
                            max="{{ $max }}"
                            value="50"
                            class="w-32 rounded-xl border border-navy/20 bg-white px-4 py-2.5 text-base font-mono text-navy focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                        >
                        <span class="text-sm text-navy/55">destinatarios (máx {{ $max }})</span>
                    </div>
                </div>

                <p class="text-xs text-navy/55 font-mono">
                    <span id="recipient-count-live" class="text-navy font-medium">50</span> emails · ranking automático por afinidad con la estrategia
                </p>
            </div>

            {{-- ═══ Follow-ups ═══ --}}
            <details class="group border-t border-navy/10 pt-8" @if ($campaign->followups_enabled) open @endif>
                <summary class="cursor-pointer text-sm font-medium flex items-center gap-2 select-none list-none">
                    <span class="inline-block transition-transform group-open:rotate-90 text-navy/40">›</span>
                    Activar follow-ups automáticos
                    <span class="text-xs text-navy/40 font-normal">— acoso escalado hasta que hagan click</span>
                </summary>

                <div class="mt-6 space-y-5 pl-5 border-l-2 border-copper/40">
                    <p class="text-xs text-navy/55 leading-relaxed max-w-xl">
                        Si los destinatarios no reaccionan, Roomie genera emails de seguimiento cada vez más insistentes (sube la agresividad y la manipulación +1 por intento, cap en 5) hasta que hagan click o se den de baja. Usan <strong class="text-navy">la misma IA que la campaña original</strong>@if ($providerDisplay) — <span class="font-mono text-navy">{{ $providerDisplay }}</span>@endif.
                    </p>

                    @if ($hasStoredKey)
                        <div class="flex items-start gap-2.5 text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2.5 max-w-xl">
                            <svg class="w-3.5 h-3.5 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            <div class="leading-relaxed">
                                <strong class="font-semibold">Clave guardada.</strong> No tienes que volver a pegarla.
                                @if ($campaign->api_key_retention_expires_at)
                                    Expira el {{ $campaign->api_key_retention_expires_at->translatedFormat('d M Y') }}.
                                @endif
                            </div>
                        </div>
                    @else
                        <div>
                            <label for="followup_api_key" class="block text-xs text-navy/55 mb-1.5">
                                Tu API key @if ($providerLabel)<span class="text-navy font-medium">de {{ $providerLabel }}</span>@endif
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
                                La clave original ya se ha borrado. Si quieres reactivar los follow-ups, pégala de nuevo (se cifrará antes de guardarse).
                            </p>
                        </div>
                    @endif

                    <label class="flex items-start gap-2.5 text-sm cursor-pointer select-none">
                        <input type="checkbox" name="enable_followups" value="1" id="enable-followups-toggle" class="mt-1 rounded border-navy/30 text-navy focus:ring-navy/20">
                        <span class="leading-snug">
                            Activar la secuencia de follow-ups para esta campaña
                        </span>
                    </label>

                    <div id="followups-params" hidden class="flex gap-4 max-w-md">
                        <div class="flex-1">
                            <label for="followup_max_attempts" class="block text-xs text-navy/55 mb-1.5">Intentos máx.</label>
                            <select name="followup_max_attempts" id="followup_max_attempts" class="w-full rounded-xl border border-navy/20 bg-white px-3 py-2.5 text-base">
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label for="followup_cooldown_hours" class="block text-xs text-navy/55 mb-1.5">Cooldown</label>
                            <select name="followup_cooldown_hours" id="followup_cooldown_hours" class="w-full rounded-xl border border-navy/20 bg-white px-3 py-2.5 text-base">
                                <option value="1">1h (demo)</option>
                                <option value="24">24h</option>
                                <option value="48" selected>48h</option>
                                <option value="72">72h</option>
                                <option value="168">1 sem</option>
                            </select>
                        </div>
                    </div>
                </div>
            </details>

            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition w-full sm:w-auto">
                Enviar ahora
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </button>
        </form>

        <script>
            (function () {
                const modes = document.querySelectorAll('input[name="recipient_mode"]');
                const customWrapper = document.getElementById('custom-count-wrapper');
                const customInput = document.getElementById('recipient_count_custom');
                const live = document.getElementById('recipient-count-live');
                const max = {{ $max }};
                const followupsToggle = document.getElementById('enable-followups-toggle');
                const followupsParams = document.getElementById('followups-params');

                function applyMode() {
                    const selected = document.querySelector('input[name="recipient_mode"]:checked');
                    if (!selected) return;
                    const value = selected.value;
                    customWrapper.hidden = (value !== 'custom');
                    if (value === 'custom') {
                        live.textContent = customInput.value || '0';
                    } else if (value === 'all') {
                        live.textContent = max;
                    } else {
                        live.textContent = value;
                    }
                }

                modes.forEach(r => r.addEventListener('change', applyMode));
                customInput.addEventListener('input', () => {
                    if (parseInt(customInput.value, 10) > max) customInput.value = max;
                    if (parseInt(customInput.value, 10) < 1) customInput.value = 1;
                    if (document.querySelector('input[name="recipient_mode"]:checked').value === 'custom') {
                        live.textContent = customInput.value || '0';
                    }
                });
                applyMode();

                function applyFollowups() {
                    if (!followupsToggle || !followupsParams) return;
                    followupsParams.hidden = !followupsToggle.checked;
                }
                if (followupsToggle) {
                    followupsToggle.addEventListener('change', applyFollowups);
                    applyFollowups();
                }
            })();
        </script>
    @endif
</section>
