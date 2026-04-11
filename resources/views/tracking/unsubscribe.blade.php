<x-layouts.app title="Darse de baja">
    <div class="max-w-md mx-auto py-10 sm:py-14 text-center">
        <svg class="w-6 h-6 text-copper mx-auto mb-6" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>

        @if ($confirmed || $alreadyUnsubscribed)
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-4">
                Listo.<br>
                <span class="text-copper">No recibirás más correos</span> de Roomie.
            </h1>
            <p class="text-navy/60 leading-relaxed mb-8">
                Tu dirección <span class="font-mono text-navy">{{ $recipient->email }}</span>
                ya no aparecerá en ninguna campaña futura.
            </p>
            <p class="text-xs text-navy/45">
                Si cambias de opinión, escríbenos y volveremos a incluirte manualmente.
            </p>
        @else
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-4">
                ¿Quieres <span class="text-copper">dejar de recibir</span> nuestros correos?
            </h1>
            <p class="text-navy/60 leading-relaxed mb-8">
                Dejaremos de enviar mensajes a <span class="font-mono text-navy">{{ $recipient->email }}</span>
                en esta y futuras campañas. Puedes volver a inscribirte después escribiéndonos.
            </p>

            <form method="POST" action="{{ $confirmUrl }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-navy text-cream pl-6 pr-5 py-3.5 rounded-full font-medium hover:bg-navy-light transition">
                    Confirmar baja
                    <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                </button>
            </form>
        @endif
    </div>
</x-layouts.app>
