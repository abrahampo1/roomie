<x-layouts.app title="Documentación del API">
    @php
        $baseUrl = url('/api/v1');
    @endphp

    <div class="grid grid-cols-12 gap-8 lg:gap-12">
        {{-- Sidebar nav --}}
        <aside class="col-span-12 lg:col-span-3 lg:sticky lg:top-24 lg:self-start">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-4">
                Roomie API · v1
            </p>
            <nav class="text-sm space-y-1">
                <a href="#introduction" class="block py-1 text-navy/65 hover:text-navy transition">Introducción</a>
                <a href="#auth" class="block py-1 text-navy/65 hover:text-navy transition">Autenticación</a>
                <a href="#quickstart" class="block py-1 text-navy/65 hover:text-navy transition">Ejemplo completo</a>
                <p class="pt-4 pb-1 text-[10px] uppercase tracking-widest text-navy/40 font-mono">Endpoints</p>
                <a href="#health" class="block py-1 text-navy/65 hover:text-navy transition">GET /health</a>
                <a href="#providers" class="block py-1 text-navy/65 hover:text-navy transition">GET /providers</a>
                <a href="#campaigns-list" class="block py-1 text-navy/65 hover:text-navy transition">GET /campaigns</a>
                <a href="#campaigns-create" class="block py-1 text-navy/65 hover:text-navy transition">POST /campaigns</a>
                <a href="#campaigns-show" class="block py-1 text-navy/65 hover:text-navy transition">GET /campaigns/:id</a>
                <a href="#campaigns-status" class="block py-1 text-navy/65 hover:text-navy transition">GET /campaigns/:id/status</a>
                <a href="#campaigns-refine" class="block py-1 text-navy/65 hover:text-navy transition">POST /campaigns/:id/refine-creative</a>
                <a href="#campaigns-send" class="block py-1 text-navy/65 hover:text-navy transition">POST /campaigns/:id/send</a>
                <a href="#campaigns-stop" class="block py-1 text-navy/65 hover:text-navy transition">POST /campaigns/:id/stop-followups</a>
                <a href="#campaigns-stats" class="block py-1 text-navy/65 hover:text-navy transition">GET /campaigns/:id/stats</a>
                <a href="#campaigns-recipients" class="block py-1 text-navy/65 hover:text-navy transition">GET /campaigns/:id/recipients</a>
                <a href="#toggle-conversion" class="block py-1 text-navy/65 hover:text-navy transition">POST toggle-conversion</a>
                <p class="pt-4 pb-1 text-[10px] uppercase tracking-widest text-navy/40 font-mono">Ref</p>
                <a href="#errors" class="block py-1 text-navy/65 hover:text-navy transition">Errores</a>
                <a href="#rate-limits" class="block py-1 text-navy/65 hover:text-navy transition">Rate limits</a>
            </nav>
        </aside>

        <div class="col-span-12 lg:col-span-9 min-w-0">

            {{-- ═══ Introduction ═══ --}}
            <section id="introduction" class="mb-16 scroll-mt-24">
                <p class="font-mono text-[11px] text-navy/40 uppercase tracking-[0.18em] mb-3">Documentación</p>
                <h1 class="font-[Fredoka] font-semibold text-4xl sm:text-5xl tracking-tight leading-[1.05] mb-5">
                    Roomie <span class="text-copper">API</span>
                </h1>
                <p class="text-navy/65 leading-relaxed text-[15px] sm:text-base max-w-2xl mb-5">
                    La API de Roomie expone el pipeline completo de 4 agentes — analista, estratega, creativo, auditor — más el envío de emails, tracking, estadísticas y secuencias de follow-up autónomas. Todo JSON, todo sobre HTTPS, todo autenticado con un bearer token.
                </p>
                <p class="text-navy/65 leading-relaxed text-[15px] sm:text-base max-w-2xl mb-3">
                    Casos de uso:
                </p>
                <ul class="text-navy/65 leading-relaxed text-[15px] sm:text-base space-y-1.5 list-disc list-inside ml-2 mb-6">
                    <li>Integrar Roomie con tu CRM hotelero y disparar campañas desde ahí.</li>
                    <li>Programar envíos recurrentes vía cron o Zapier/Make.</li>
                    <li>Leer métricas en vivo desde tu dashboard de BI.</li>
                    <li>Construir un bot de Slack que cree campañas desde lenguaje natural.</li>
                </ul>
                <div class="grid sm:grid-cols-2 gap-4 max-w-2xl">
                    <div class="p-4 rounded-xl bg-sand-light/60 border border-navy/10">
                        <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-1">Base URL</p>
                        <p class="font-mono text-sm break-all">{{ $baseUrl }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-sand-light/60 border border-navy/10">
                        <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-1">Versión actual</p>
                        <p class="font-[Fredoka] font-semibold text-lg">v1</p>
                    </div>
                </div>
            </section>

            {{-- ═══ Auth ═══ --}}
            <section id="auth" class="mb-16 scroll-mt-24">
                <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight mb-4">Autenticación</h2>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl">
                    Cada request protegida lleva una cabecera <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">Authorization: Bearer {token}</code>. Genera el token desde el dashboard en <a href="{{ route('settings.api-token.show') }}" class="underline underline-offset-4 decoration-navy/30 hover:decoration-navy">Ajustes → API</a>. Solo se muestra una vez al crearlo — guárdalo en un vault.
                </p>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl">
                    Roomie almacena solo el hash SHA-256 del token, nunca el token en claro. Puedes revocarlo en cualquier momento desde el mismo panel y cualquier integración que lo esté usando dejará de funcionar inmediatamente.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto leading-relaxed mt-5">curl {{ $baseUrl }}/providers \
  -H "Authorization: Bearer sk-roomie-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Accept: application/json"</pre>
            </section>

            {{-- ═══ Quickstart ═══ --}}
            <section id="quickstart" class="mb-16 scroll-mt-24">
                <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight mb-4">Ejemplo completo</h2>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl">
                    Crea una campaña, espera a que el pipeline complete, lánzala, y pide las estadísticas. Todo con <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">curl</code>.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto leading-relaxed">TOKEN="sk-roomie-xxxxxxxxxxxx..."
BASE="{{ $baseUrl }}"

# 1. Crear la campaña
CAMPAIGN_ID=$(curl -s -X POST $BASE/campaigns \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Junio cultural Granada",
    "objective": "Subir la ocupación del Aurea Catedral en Granada durante junio",
    "aggressiveness": 2,
    "manipulation": 2,
    "provider": "anthropic",
    "api_key": "sk-ant-api03-..."
  }' | jq -r .id)

# 2. Poll hasta que termine el pipeline (~60s)
while [ "$(curl -s $BASE/campaigns/$CAMPAIGN_ID/status \
    -H "Authorization: Bearer $TOKEN" | jq -r .status)" != "completed" ]; do
  sleep 3
done

# 3. Lanzar el envío a los 50 recipients mejor rankeados
curl -X POST $BASE/campaigns/$CAMPAIGN_ID/send \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"recipient_mode": "50", "enable_followups": true}'

# 4. Esperar un poco y pedir el dashboard
sleep 30
curl $BASE/campaigns/$CAMPAIGN_ID/stats \
  -H "Authorization: Bearer $TOKEN" | jq</pre>
            </section>

            {{-- ═══ GET /health ═══ --}}
            <section id="health" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/health</code>
                    <span class="text-[10px] text-navy/40 uppercase tracking-wider">Sin auth</span>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Health check. Útil para load balancers y para verificar que el token se lee bien antes de hacer llamadas reales.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">{
    "ok": true,
    "version": "v1",
    "service": "roomie-api"
}</pre>
            </section>

            {{-- ═══ GET /providers ═══ --}}
            <section id="providers" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/providers</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Lista los LLM providers que Roomie sabe usar. Úsalo para validar tu selector de provider antes de hacer un <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">POST /campaigns</code>.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">{
    "providers": [
        { "id": "anthropic", "label": "Anthropic Claude", "requires_custom_fields": false },
        { "id": "google",    "label": "Google Gemini",    "requires_custom_fields": false },
        { "id": "openai",    "label": "OpenAI",           "requires_custom_fields": false },
        { "id": "deepseek",  "label": "DeepSeek",         "requires_custom_fields": false },
        { "id": "custom",    "label": "Custom",           "requires_custom_fields": true  }
    ]
}</pre>
            </section>

            {{-- ═══ GET /campaigns ═══ --}}
            <section id="campaigns-list" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Lista paginada de campañas del usuario dueño del token, ordenadas por fecha de creación descendente.
                </p>
                <div class="mb-4">
                    <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-2">Query params</p>
                    <table class="text-sm text-navy/70 w-full max-w-2xl">
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs w-32"><code>per_page</code></td>
                            <td class="py-2">Entero, default 25. Cuántas campañas por página.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>page</code></td>
                            <td class="py-2">Entero, default 1. Número de página.</td>
                        </tr>
                    </table>
                </div>
            </section>

            {{-- ═══ POST /campaigns ═══ --}}
            <section id="campaigns-create" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-copper bg-copper/10 border border-copper/30 px-2 py-0.5 rounded uppercase tracking-wider font-bold">POST</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Crea una campaña y encola el pipeline de 4 agentes. Devuelve <strong>202 Accepted</strong> con un <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">poll_url</code> para consultar el estado.
                </p>
                <div class="mb-4">
                    <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-2">Body</p>
                    <table class="text-sm text-navy/70 w-full max-w-2xl">
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs w-40"><code>objective</code></td>
                            <td class="py-2">String, 10–1000 chars. <strong>Requerido.</strong></td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>name</code></td>
                            <td class="py-2">String opcional, máx 120 chars.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>aggressiveness</code></td>
                            <td class="py-2">Entero 0–5. 0 = informativa, 5 = agresiva.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>manipulation</code></td>
                            <td class="py-2">Entero 0–5. 0 = neutral, 5 = dark patterns.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>provider</code></td>
                            <td class="py-2">Uno de <code class="font-mono text-xs">anthropic</code>, <code class="font-mono text-xs">google</code>, <code class="font-mono text-xs">openai</code>, <code class="font-mono text-xs">deepseek</code>, <code class="font-mono text-xs">custom</code>.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>api_key</code></td>
                            <td class="py-2">Tu clave del provider. Se cifra con la APP_KEY.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>api_base_url</code></td>
                            <td class="py-2">Requerido si <code>provider=custom</code>. Endpoint compatible con chat completions de OpenAI.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>api_model</code></td>
                            <td class="py-2">Requerido si <code>provider=custom</code>. Nombre del modelo.</td>
                        </tr>
                    </table>
                </div>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">// Response 202
{
    "id": 42,
    "status": "pending",
    "poll_url": "{{ $baseUrl }}/campaigns/42/status"
}</pre>
            </section>

            {{-- ═══ GET /campaigns/:id ═══ --}}
            <section id="campaigns-show" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Detalle completo de la campaña — todos los outputs de los agentes, la configuración de envío y follow-ups, y los metadatos. El <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">api_key</code> nunca se devuelve.
                </p>
            </section>

            {{-- ═══ GET /campaigns/:id/status ═══ --}}
            <section id="campaigns-status" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/status</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Respuesta ligera para hacer polling mientras el pipeline corre. Incluye <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">has_analysis</code>, <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">has_strategy</code>, etc. y previews cortos a medida que cada agente completa.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">{
    "id": 42,
    "status": "processing",
    "quality_score": null,
    "has_analysis": true,
    "has_strategy": true,
    "has_creative": false,
    "has_audit": false,
    "analysis_preview": { "segments_count": 4, "focus_segment": "Parejas internacionales" },
    "strategy_preview": { "hotel_name": "Aurea Catedral", "channel": "email", "segment": "Parejas internacionales" },
    "creative_preview": null,
    "audit_preview": null
}</pre>
            </section>

            {{-- ═══ POST /campaigns/:id/refine-creative ═══ --}}
            <section id="campaigns-refine" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-copper bg-copper/10 border border-copper/30 px-2 py-0.5 rounded uppercase tracking-wider font-bold">POST</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/refine-creative</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Aplica un ajuste en lenguaje natural al creative actual. La IA lee el objetivo, la estrategia y el creative vigente y devuelve un creative actualizado que solo toca lo que le pediste. Requiere que la campaña todavía tenga su clave API retenida — si ha sido borrada, devuelve <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">key_not_available</code>.
                </p>
                <div class="mb-4">
                    <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-2">Body</p>
                    <table class="text-sm text-navy/70 w-full max-w-2xl">
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs w-48"><code>refinement_prompt</code></td>
                            <td class="py-2">String 5–500 chars. Ejemplos: <em>"asunto más corto"</em>, <em>"añade un pull-quote sobre el atardecer"</em>, <em>"suaviza el tono del último párrafo"</em>.</td>
                        </tr>
                    </table>
                </div>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">// Response 200 — el Campaign completo con el creative actualizado
{
    "data": {
        "id": 42,
        "creative": {
            "subject_line": "Granada, ahora.",
            "headline": "Los atardeceres más lentos del verano",
            "body_html": "...",
            ...
        },
        ...
    }
}</pre>
            </section>

            {{-- ═══ POST /campaigns/:id/send ═══ --}}
            <section id="campaigns-send" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-copper bg-copper/10 border border-copper/30 px-2 py-0.5 rounded uppercase tracking-wider font-bold">POST</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/send</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Selecciona recipients con el heurístico interno y dispatcha el envío. Opcionalmente activa la secuencia de follow-ups autónomos.
                </p>
                <div class="mb-4">
                    <p class="text-[10px] uppercase tracking-widest text-navy/40 font-mono mb-2">Body</p>
                    <table class="text-sm text-navy/70 w-full max-w-2xl">
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs w-48"><code>recipient_mode</code></td>
                            <td class="py-2">Uno de <code class="font-mono text-xs">50</code>, <code class="font-mono text-xs">100</code>, <code class="font-mono text-xs">200</code>, <code class="font-mono text-xs">custom</code>, <code class="font-mono text-xs">all</code>.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>recipient_count_custom</code></td>
                            <td class="py-2">Entero. Requerido si <code>recipient_mode=custom</code>.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>enable_followups</code></td>
                            <td class="py-2">Bool. Si <code>true</code>, activa la secuencia de seguimiento.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>followup_max_attempts</code></td>
                            <td class="py-2">Entero 2–5. Default 3.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>followup_cooldown_hours</code></td>
                            <td class="py-2">Entero 1–168. Default 48.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2 pr-4 font-mono text-xs"><code>followup_api_key</code></td>
                            <td class="py-2">Solo si la clave original ha sido borrada (retención expirada). Normalmente no hace falta.</td>
                        </tr>
                    </table>
                </div>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">// Response 200
{
    "dispatched": 50,
    "total_queued": 50
}</pre>
            </section>

            {{-- ═══ POST /campaigns/:id/stop-followups ═══ --}}
            <section id="campaigns-stop" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-copper bg-copper/10 border border-copper/30 px-2 py-0.5 rounded uppercase tracking-wider font-bold">POST</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/stop-followups</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Detiene la secuencia de follow-ups y borra inmediatamente la clave API retenida del servidor. Es idempotente: llamarlo dos veces devuelve el mismo resultado.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">// Response 200
{
    "stopped": true,
    "key_wiped": true
}</pre>
            </section>

            {{-- ═══ GET /campaigns/:id/stats ═══ --}}
            <section id="campaigns-stats" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/stats</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Dashboard completo en JSON: funnel, time series, breakdown por país, breakdown por segmento (edad + género), performance por intento de follow-up, y métricas agregadas.
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto">{
    "data": {
        "campaign_id": 42,
        "summary": { "sent": 50, "opened": 18, "clicked": 5, "converted": 5, "open_rate": 36, ... },
        "funnel": [
            { "label": "Disparados",  "count": 50, "pct_total": 100, "pct_prev": 100 },
            { "label": "Entregados",  "count": 48, "pct_total": 96,  "pct_prev": 96 },
            { "label": "Abiertos",    "count": 18, "pct_total": 36,  "pct_prev": 37.5 },
            { "label": "Clickados",   "count": 5,  "pct_total": 10,  "pct_prev": 27.8 },
            { "label": "Convertidos", "count": 5,  "pct_total": 10,  "pct_prev": 100 }
        ],
        "time_series": { "buckets": [...], "has_enough_data": true },
        "country_breakdown": [ { "country": "ES", "sent": 25, "opened": 10, "clicked": 3, "pct": 100 }, ... ],
        "segment_breakdown": { "age_range": [...], "gender": [...] },
        "followup_performance": [ { "attempt": 1, "sent": 50, "opened": 18, "clicked": 5, ... } ]
    }
}</pre>
            </section>

            {{-- ═══ GET /campaigns/:id/recipients ═══ --}}
            <section id="campaigns-recipients" class="mb-14 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wider font-bold">GET</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/recipients</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Lista paginada de recipients con su estado actual, contadores de opens/clicks, número de intentos enviados y todos los timestamps del lifecycle (open, click, convert, bounce, unsubscribe).
                </p>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    El <code class="font-mono text-xs bg-navy/5 px-1 py-0.5 rounded">tracking_token</code> de cada recipient es un secreto interno y nunca se incluye en la respuesta.
                </p>
            </section>

            {{-- ═══ POST toggle-conversion ═══ --}}
            <section id="toggle-conversion" class="mb-16 scroll-mt-24">
                <div class="flex items-baseline gap-3 mb-3 flex-wrap">
                    <span class="font-mono text-[10px] text-copper bg-copper/10 border border-copper/30 px-2 py-0.5 rounded uppercase tracking-wider font-bold">POST</span>
                    <code class="font-mono text-sm sm:text-base font-medium">/campaigns/:id/recipients/:recipient/toggle-conversion</code>
                </div>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl text-[15px]">
                    Marca manualmente a un recipient como convertido (o deshace la marca). Útil cuando tu sistema de reservas real confirma una venta y quieres que Roomie pare los follow-ups para esa persona.
                </p>
            </section>

            {{-- ═══ Errors ═══ --}}
            <section id="errors" class="mb-16 scroll-mt-24 pt-10 border-t border-navy/10">
                <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight mb-4">Errores</h2>
                <p class="text-navy/65 leading-relaxed mb-5 max-w-2xl">
                    Todas las respuestas de error usan este shape:
                </p>
                <pre class="font-mono text-xs bg-navy text-cream p-4 rounded-xl overflow-x-auto mb-6">{
    "error": "short_code",
    "message": "Human-readable explanation."
}</pre>
                <table class="text-sm text-navy/70 w-full max-w-2xl">
                    <thead>
                        <tr class="border-b-2 border-navy/15">
                            <th class="py-2 pr-4 text-left font-mono text-[10px] uppercase tracking-widest text-navy/40 w-20">Status</th>
                            <th class="py-2 pr-4 text-left font-mono text-[10px] uppercase tracking-widest text-navy/40 w-48">Error code</th>
                            <th class="py-2 text-left font-mono text-[10px] uppercase tracking-widest text-navy/40">Cuándo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">401</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">unauthenticated</td>
                            <td class="py-2.5">Sin header, header vacío o token no encontrado.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">403</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">forbidden</td>
                            <td class="py-2.5">El recurso pertenece a otro usuario.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">404</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">—</td>
                            <td class="py-2.5">Recurso inexistente.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">422</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">validation_failed</td>
                            <td class="py-2.5">Body inválido. Incluye <code class="font-mono text-xs">errors</code> field-by-field.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">422</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">campaign_not_ready</td>
                            <td class="py-2.5">Intentaste hacer <code class="font-mono text-xs">send</code> antes de que el pipeline completase.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">422</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">no_recipients</td>
                            <td class="py-2.5">El selector no encontró clientes que encajaran con la estrategia.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">422</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">no_key_for_followups</td>
                            <td class="py-2.5">Activaste follow-ups pero la clave original se había borrado y no enviaste <code class="font-mono text-xs">followup_api_key</code>.</td>
                        </tr>
                        <tr class="border-b border-navy/10">
                            <td class="py-2.5 pr-4 font-mono text-xs">429</td>
                            <td class="py-2.5 pr-4 font-mono text-xs">—</td>
                            <td class="py-2.5">Rate limit excedido. Respeta el header <code class="font-mono text-xs">Retry-After</code>.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            {{-- ═══ Rate limits ═══ --}}
            <section id="rate-limits" class="mb-16 scroll-mt-24">
                <h2 class="font-[Fredoka] font-semibold text-2xl sm:text-3xl tracking-tight mb-4">Rate limits</h2>
                <p class="text-navy/65 leading-relaxed mb-4 max-w-2xl">
                    Los endpoints de lectura tienen un límite de <strong>60 requests por minuto</strong>. Los endpoints de escritura (crear campaña, enviar, stop-followups, toggle-conversion) tienen un límite más bajo de <strong>20 por minuto</strong> porque cada uno dispara trabajo asíncrono costoso.
                </p>
                <p class="text-navy/65 leading-relaxed max-w-2xl">
                    Cuando superes un límite recibirás 429 con una cabecera <code class="font-mono text-xs bg-navy/5 px-1.5 py-0.5 rounded">Retry-After</code> indicando cuántos segundos esperar.
                </p>
            </section>

        </div>
    </div>
</x-layouts.app>
