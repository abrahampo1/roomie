<x-layouts.app title="Webhooks">
    <div class="max-w-3xl">
        <a href="{{ route('home') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Inicio
        </a>

        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mt-4 mb-3">
            Ajustes · Webhooks
        </p>
        <div class="flex items-start justify-between gap-6 flex-wrap mb-3">
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05]">
                Webhooks
            </h1>
            <a href="{{ route('settings.webhooks.create') }}"
               class="inline-flex items-center gap-2 bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                + Nuevo webhook
            </a>
        </div>
        <p class="text-navy/60 leading-relaxed mb-10 max-w-xl">
            Suscríbete a eventos del pipeline y del envío de emails y recíbelos en tu propio endpoint HTTPS con firma HMAC.
            <a href="{{ route('docs') }}#webhooks-intro" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">Documentación</a>.
        </p>

        @if (session('message'))
            <div class="rounded-xl border border-navy/15 bg-sand-light/60 p-4 mb-6 text-sm text-navy/75">
                {{ session('message') }}
            </div>
        @endif

        @if ($webhooks->isEmpty())
            <div class="rounded-2xl border border-dashed border-navy/20 p-10 text-center">
                <p class="text-navy/55 leading-relaxed mb-5 max-w-md mx-auto">
                    Todavía no tienes webhooks configurados. Crea uno para empezar a recibir eventos en tiempo real.
                </p>
                <a href="{{ route('settings.webhooks.create') }}"
                   class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3 rounded-full font-medium hover:bg-navy-light transition">
                    Crear webhook
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($webhooks as $webhook)
                    <a href="{{ route('settings.webhooks.show', $webhook) }}"
                       class="block rounded-2xl border border-navy/15 bg-white p-5 hover:border-navy/30 transition">
                        <div class="flex items-start gap-4">
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
                                <p class="font-mono text-xs text-navy/55 truncate mb-2">{{ $webhook->url }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (array_slice($webhook->events, 0, 4) as $event)
                                        <span class="font-mono text-[10px] text-navy/60 bg-navy/5 px-2 py-0.5 rounded">
                                            {{ $event }}
                                        </span>
                                    @endforeach
                                    @if (count($webhook->events) > 4)
                                        <span class="font-mono text-[10px] text-navy/40 px-2 py-0.5">
                                            +{{ count($webhook->events) - 4 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
