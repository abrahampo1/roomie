<x-layouts.app title="API token">
    <div class="max-w-2xl">
        <a href="{{ route('home') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
            ← Inicio
        </a>

        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mt-4 mb-3">
            Ajustes · API
        </p>
        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            Token de acceso al API
        </h1>
        <p class="text-navy/60 leading-relaxed mb-10 max-w-xl">
            Usa este token para integrar Roomie con otros sistemas — tu CRM, un
            script propio, un Zapier. El endpoint raíz del API es
            <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">/api/v1</code>
            y todos los métodos protegidos esperan una cabecera
            <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">Authorization: Bearer {token}</code>.
            <a href="{{ route('docs') }}" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">Lee la documentación completa</a>.
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

        {{-- Quick reference --}}
        <div class="pt-8 border-t border-navy/10">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/40 mb-4">
                Ejemplo rápido
            </p>
            <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto leading-relaxed">curl {{ url('/api/v1/campaigns') }} \
  -H "Authorization: Bearer {{ $newToken ?? 'TU_TOKEN_AQUI' }}" \
  -H "Accept: application/json"</pre>
        </div>
    </div>
</x-layouts.app>
