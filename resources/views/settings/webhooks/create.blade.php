<x-layouts.app title="Nuevo webhook">
    <div class="max-w-2xl">
        <a href="{{ route('settings.webhooks.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Webhooks
        </a>

        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mt-4 mb-3">
            Ajustes · Webhooks · Nuevo
        </p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            Nuevo webhook
        </h1>
        <p class="text-navy/60 leading-relaxed mb-10 max-w-xl">
            Cada vez que ocurra uno de los eventos seleccionados, Roomie enviará un POST firmado a la URL indicada. El secret para verificar la firma se te mostrará una sola vez tras crearlo.
        </p>

        <form method="POST" action="{{ route('settings.webhooks.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">
                    Nombre
                </label>
                <input
                    type="text"
                    id="name"
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
                <label for="url" class="block font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">
                    URL (https)
                </label>
                <input
                    type="url"
                    id="url"
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

                <div class="border-t border-navy/10 pt-4 mt-4 space-y-2">
                    <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mb-2">Campaign lifecycle</p>
                    @foreach (\App\Services\Webhooks\WebhookEvents::CAMPAIGN_EVENTS as $event)
                        <label class="flex items-center gap-3 text-sm cursor-pointer">
                            <input
                                type="checkbox"
                                name="events[]"
                                value="{{ $event }}"
                                class="w-4 h-4 rounded border-navy/30 text-navy focus:ring-navy/30"
                                {{ in_array($event, old('events', [])) ? 'checked' : '' }}
                            >
                            <code class="font-mono text-xs text-navy/75">{{ $event }}</code>
                        </label>
                    @endforeach

                    <p class="font-mono text-[10px] uppercase tracking-widest text-navy/40 mt-5 mb-2">Recipient lifecycle</p>
                    @foreach (\App\Services\Webhooks\WebhookEvents::RECIPIENT_EVENTS as $event)
                        <label class="flex items-center gap-3 text-sm cursor-pointer">
                            <input
                                type="checkbox"
                                name="events[]"
                                value="{{ $event }}"
                                class="w-4 h-4 rounded border-navy/30 text-navy focus:ring-navy/30"
                                {{ in_array($event, old('events', [])) ? 'checked' : '' }}
                            >
                            <code class="font-mono text-xs text-navy/75">{{ $event }}</code>
                        </label>
                    @endforeach
                </div>
                @error('events')
                    <p class="text-sm text-red-700 mt-3">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit"
                        class="bg-navy text-cream px-6 py-3 rounded-full text-sm font-medium hover:bg-navy-light transition">
                    Crear webhook
                </button>
                <a href="{{ route('settings.webhooks.index') }}"
                   class="border border-navy/20 text-navy/70 px-6 py-3 rounded-full text-sm font-medium hover:bg-navy/[0.03] transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
