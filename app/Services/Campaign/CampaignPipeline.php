<?php

namespace App\Services\Campaign;

use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use App\Services\LLM\LlmClient;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampaignPipeline
{
    private int $aggressiveness = 2;

    private int $persuasionPatterns = 2;

    public function __construct(
        private LlmClient $client,
        private MarketIntelligenceService $market,
    ) {}

    public function run(Campaign $campaign): void
    {
        $campaign->update(['status' => 'processing']);

        $this->aggressiveness = (int) ($campaign->aggressiveness ?? 2);
        $this->persuasionPatterns = (int) ($campaign->persuasion_patterns ?? 2);

        try {
            $hotelsContext = $this->buildHotelsContext();
            $customersContext = $this->buildCustomersContext();
            $marketContext = $this->market->getMarketContext($this->collectUniqueCities());

            $analysis = $this->runAnalyst($campaign->objective, $hotelsContext, $customersContext, $marketContext);
            $campaign->update(['analysis' => $analysis]);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.analysis_completed', [
                'campaign_id' => $campaign->id,
                'analysis' => $analysis,
            ]);

            $strategy = $this->runStrategist($campaign->objective, $analysis, $hotelsContext, $marketContext);
            $campaign->update(['strategy' => $strategy]);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.strategy_completed', [
                'campaign_id' => $campaign->id,
                'strategy' => $strategy,
            ]);

            $creative = $this->runCreative($campaign, $strategy);
            $campaign->update(['creative' => $creative]);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.creative_completed', [
                'campaign_id' => $campaign->id,
                'creative' => $creative,
            ]);

            $audit = $this->runAuditor($campaign->objective, $strategy, $creative);
            $campaign->update([
                'audit' => $audit,
                'quality_score' => $audit['quality_score'] ?? null,
                'status' => 'completed',
            ]);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.audit_completed', [
                'campaign_id' => $campaign->id,
                'audit' => $audit,
                'quality_score' => $audit['quality_score'] ?? null,
            ]);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.completed', [
                'campaign' => (new CampaignResource($campaign->fresh()))->toArray(new Request),
            ]);
        } catch (\Throwable $e) {
            Log::error('Campaign pipeline failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            $campaign->update(['status' => 'failed']);
            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function runAnalyst(string $objective, string $hotels, string $customers, string $marketContext): array
    {
        $marketBlock = $marketContext !== ''
            ? "CONTEXTO DE MERCADO:\n{$marketContext}\n\n"
            : '';

        $prompt = <<<PROMPT
        Eres el Agente Analista de campañas de marketing hotelero para Eurostars Hotel Company.

        OBJETIVO DE NEGOCIO: {$objective}

        DATOS DE HOTELES:
        {$hotels}

        {$marketBlock}DATOS DE CLIENTES (muestra):
        {$customers}

        Analiza los datos y responde en JSON con esta estructura exacta:
        {
            "segments": [
                {
                    "name": "nombre del segmento",
                    "description": "descripcion",
                    "size": numero_estimado,
                    "avg_adr": numero,
                    "preferred_destinations": ["ciudad1", "ciudad2"],
                    "booking_behavior": "descripcion del comportamiento"
                }
            ],
            "market_insights": ["insight1", "insight2", "insight3"],
            "seasonal_factors": ["factor1", "factor2"],
            "recommended_focus_segment": "nombre del segmento mas relevante para el objetivo"
        }

        Responde SOLO el JSON, sin markdown ni explicaciones.
        PROMPT;

        return $this->client->complete($prompt, 'analyst');
    }

    private function runStrategist(string $objective, array $analysis, string $hotels, string $marketContext): array
    {
        $analysisJson = json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $intensity = $this->intensityGuidance();
        $marketBlock = $marketContext !== ''
            ? "CONTEXTO DE MERCADO:\n{$marketContext}\nCuando cites este contexto de mercado, sé específico con números (ocupación, temperaturas, países de origen).\n\n"
            : '';

        $prompt = <<<PROMPT
        Eres el Agente Estratega de campañas de marketing hotelero para Eurostars Hotel Company.

        OBJETIVO DE NEGOCIO: {$objective}

        ANÁLISIS PREVIO:
        {$analysisJson}

        HOTELES DISPONIBLES:
        {$hotels}

        {$marketBlock}{$intensity}

        Diseña la estrategia de campaña. El "tone" y el "key_message" DEBEN reflejar los niveles de intensidad indicados. Responde en JSON con esta estructura exacta:
        {
            "campaign_name": "nombre creativo de la campaña",
            "target_segment": {
                "name": "segmento objetivo",
                "persona": "descripcion detallada del cliente tipo",
                "pain_points": ["punto1", "punto2"],
                "motivations": ["motivacion1", "motivacion2"]
            },
            "recommended_hotel": {
                "name": "nombre del hotel",
                "city": "ciudad",
                "why": "por que este hotel encaja con este segmento"
            },
            "timing": {
                "best_period": "cuando lanzar",
                "lead_time_days": numero,
                "reason": "por que este momento"
            },
            "channel": "email|push|social|sms",
            "tone": "descripcion del tono comunicativo",
            "key_message": "el mensaje central en una frase"
        }

        Responde SOLO el JSON, sin markdown ni explicaciones.
        PROMPT;

        return $this->client->complete($prompt, 'strategist');
    }

    private function runCreative(Campaign $campaign, array $strategy): array
    {
        $objective = $campaign->objective;
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $intensity = $this->intensityGuidance();
        $designGuide = $this->creativeDesignGuide($campaign);
        $brandContext = $this->buildBrandContext($campaign);
        $imageContext = $this->buildImageBankContext($campaign);
        $hotelName = $strategy['recommended_hotel']['name'] ?? 'el hotel destacado';
        $hotelCity = $strategy['recommended_hotel']['city'] ?? '';
        $tone = $strategy['tone'] ?? '';
        $keyMessage = $strategy['key_message'] ?? '';

        $prompt = <<<PROMPT
        Eres el Agente Creativo de Roomie. Escribes emails editoriales para Eurostars Hotel Company que un diseñador senior publicaría sin tocarlos.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA:
        {$strategyJson}

        Tono: {$tone}
        Mensaje clave (debe aparecer literalmente en el body): {$keyMessage}
        Hotel destacado: {$hotelName} en {$hotelCity}

        {$brandContext}

        {$intensity}

        {$designGuide}

        {$imageContext}

        Responde en JSON con esta estructura EXACTA:
        {
            "subject_line": "asunto del email (max 60 chars, concreto, evocativo, sin 'RE:' falsos ni ALL CAPS salvo UNA palabra)",
            "preview_text": "preview (max 90 chars) que complementa el asunto sin repetirlo",
            "headline": "titular editorial de 5-9 palabras sin puntuación final — aparece en un hero navy de 36px",
            "body_html": "HTML del cuerpo siguiendo las DIRECTRICES DE DISEÑO de arriba. Solo inline styles. ~300 palabras.",
            "cta_text": "texto del botón (2-4 palabras, verbo de acción específico — NO 'Click aquí', NO 'Más info')",
            "cta_url_slug": "slug para la URL de destino",
            "alt_formats": {
                "push_notification": "texto de push (max 100 chars)",
                "sms": "texto de SMS (max 160 chars)",
                "social_caption": "caption para redes sociales"
            },
            "visual_direction": "direccion visual/fotografica que acompañaria este email en un brief de diseño real"
        }

        IMPORTANTE: adapta subject_line, headline, body_html y cta_text a los niveles de intensidad indicados. Si agresividad y patrones de persuasión son altos (4-5), el copy debe oler a urgencia, escasez numérica y FOMO concreto. Si son bajos (0-1), el copy es puramente editorial/informativo.

        Responde SOLO el JSON, sin markdown ni explicaciones. El body_html debe ser HTML válido con los estilos inline EXACTOS indicados en las directrices.
        PROMPT;

        return $this->client->complete($prompt, 'creative');
    }

    /**
     * Shared design rules for the Creativo agent. Used by both the initial
     * creative prompt, the follow-up regeneration prompt, and the interactive
     * refinement prompt — so all three produce `body_html` that slots into
     * the same outer email shell.
     */
    private function creativeDesignGuide(?Campaign $campaign = null): string
    {
        $hasImages = $campaign && $campaign->user?->bankImages()->exists();
        $imageRule = $hasImages
            ? '- Puedes incluir imágenes del banco usando placeholders {{image:ID}} (máximo 2). Formato: <img src="{{image:ID}}" alt="descripción" style="display:block;max-width:100%;height:auto;margin:0 0 20px;border-radius:8px;">. Si ninguna es relevante, no incluyas ninguna.'
            : '- NO imágenes (no tenemos assets).';

        return <<<GUIDE
        DIRECTRICES DE DISEÑO DEL EMAIL (críticas para la calidad visual final):

        CONTEXTO DEL SHELL (lo que tú NO tienes que escribir):
        El `body_html` que generas se inserta dentro de una card editorial de 640px con este layout fijo:
        - Un hero navy con un brand mark en courier + tu `headline` en Georgia 36px
        - Tu `body_html` (lo que escribes ahora)
        - Un divider decorativo "— ✦ —" en copper
        - Un botón CTA en copper con tu `cta_text` + flecha
        - Un footer con la dirección física y el link de unsubscribe

        Por tanto: NO repitas el hotel, el titular ni un CTA dentro del body_html. Tu body_html es el cuerpo editorial entre la cabecera y el botón.

        TIPOGRAFÍA Y COLORES PERMITIDOS:
        - Fuente principal: Georgia, 'Times New Roman', serif. NO uses Inter, Fredoka ni fuentes custom (los clientes de email no las cargan).
        - Fuente mono (para captions pequeños): 'Courier New', Courier, monospace.
        - Colores:
          · #1a1a2e (navy — texto principal, headlines)
          · #1a1a2eb3 (navy 70% — texto de body)
          · #1a1a2e66 (navy 40% — captions, metadatos)
          · #c8956c (copper — único accent permitido, usar con moderación)
          · #e2d1c3 (sand — dividers/separadores)
        - Nada fuera de esta paleta. Nada de degradados. Nada de sombras.

        REGLAS DE EMAIL CLIENT:
        - SIEMPRE inline styles. NUNCA <style> tags.
        {$imageRule}
        - NO JavaScript.
        - NO SVG inline (Outlook no lo renderiza).
        - Usa `&mdash;` para em-dash, `&nbsp;` donde quieras evitar wrap.
        - Para sparkles decorativos usa el char Unicode `&#10022;` (✦).
        - No uses emojis.
        - Margin-bottom en párrafos, NO margin-top (algunos clientes los colapsan).

        ESTRUCTURA EDITORIAL RECOMENDADA (250-400 palabras):

        1. Párrafo lead (20px, primera línea memorable):
        <p style="margin:0 0 24px;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:1.5;color:#1a1a2e;">Primera frase potente con una imagen concreta.</p>

        2. 2-3 párrafos de body (17px, con breathing room):
        <p style="margin:0 0 20px;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">Párrafo con sustancia — nombres propios, horas, sensaciones, números concretos.</p>

        3. OPCIONAL (uno solo, para una frase memorable): pull-quote editorial
        <blockquote style="margin:32px 0;padding:4px 0 4px 24px;border-left:2px solid #c8956c;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:22px;line-height:1.4;color:#1a1a2e;">"La frase clave entre comillas."</blockquote>

        4. OPCIONAL (en vez del pull-quote): lista editorial de 3 highlights
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
            <tr><td style="padding:0 0 12px 0;font-family:Georgia,serif;font-size:17px;line-height:1.6;color:#1a1a2eb3;"><span style="color:#c8956c;">&mdash;&nbsp;</span>Primer highlight concreto.</td></tr>
            <tr><td style="padding:0 0 12px 0;font-family:Georgia,serif;font-size:17px;line-height:1.6;color:#1a1a2eb3;"><span style="color:#c8956c;">&mdash;&nbsp;</span>Segundo highlight con un número.</td></tr>
            <tr><td style="padding:0;font-family:Georgia,serif;font-size:17px;line-height:1.6;color:#1a1a2eb3;"><span style="color:#c8956c;">&mdash;&nbsp;</span>Tercero que lleva hacia el CTA.</td></tr>
        </table>

        5. OPCIONAL: small-caps caption antes de una sección (para darle ritmo editorial)
        <p style="margin:24px 0 8px;font-family:'Courier New',Courier,monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#1a1a2e66;">Sección</p>

        6. Frase puente final que lleva al CTA sin repetirlo literalmente:
        <p style="margin:0 0 4px;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">Una frase de cierre que prepara el click sin decir 'haz click'.</p>

        7. Sign-off (al final del todo, siempre):
        <p style="margin:28px 0 0;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:15px;color:#1a1a2e66;">&mdash; El equipo del {nombre del hotel}</p>

        PRINCIPIOS DE CONTENIDO:
        - Imagen mental concreta por párrafo: atardecer, patio, copa, rooftop, sierra con nieve, ruta empedrada. NUNCA generalidades.
        - Nombres propios reales: usa el nombre del hotel, la ciudad, platos, calles, barrios, horas exactas.
        - NO clichés: prohibido "Descubre el encanto de...", "Una experiencia única", "Sumérgete en...".
        - El lector debe VER el viaje antes de hacer click.
        - El `key_message` del strategist debe aparecer textualmente en algún punto del body.
        - Un pull-quote o una lista — NO ambos. Rompería el ritmo.

        EJEMPLO de body_html COMPLETO bien hecho (referencia de calidad):
        <p style="margin:0 0 24px;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:1.5;color:#1a1a2e;">En junio Granada vuelve a ser nuestra. Menos multitudes, menos colas, más silencio en las calles empedradas del Albaicín.</p>
        <p style="margin:0 0 20px;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">Desde el &Aacute;urea Catedral caminas tres minutos hasta la Alhambra. Diez hasta la plaza Nueva. Y desde el rooftop ves la Sierra Nevada todav&iacute;a con nieve mientras cenas ensalada de remolacha y pulpo a la brasa.</p>
        <blockquote style="margin:32px 0;padding:4px 0 4px 24px;border-left:2px solid #c8956c;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:22px;line-height:1.4;color:#1a1a2e;">"Los atardeceres m&aacute;s lentos del verano."</blockquote>
        <p style="margin:0 0 20px;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">Junio es la &uacute;nica ventana del a&ntilde;o con temperaturas perfectas, todas las terrazas abiertas, y precios que bajan un 30% respecto a julio. Dura tres semanas.</p>
        <p style="margin:28px 0 0;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:15px;color:#1a1a2e66;">&mdash; El equipo del &Aacute;urea Catedral</p>
        GUIDE;
    }

    private function runAuditor(string $objective, array $strategy, array $creative): array
    {
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $creativeJson = json_encode($creative, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $intensity = $this->intensityGuidance();

        $prompt = <<<PROMPT
        Eres el Agente Auditor de campañas de marketing hotelero para Eurostars Hotel Company.
        Tu trabajo es revisar la coherencia y calidad de una campaña antes de lanzarla.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA:
        {$strategyJson}

        CONTENIDO CREATIVO:
        {$creativeJson}

        {$intensity}

        IMPORTANTE: Evalúa la campaña con los niveles de intensidad configurados en mente. No penalices una campaña por ser "demasiado agresiva" o "demasiado persuasiva" si el usuario pidió un nivel alto — en ese caso lo correcto es que lo sea. Penaliza solo la falta de coherencia con el nivel pedido (ej. pidieron 5/5 y el copy es blando).

        Evalúa la campaña y responde en JSON con esta estructura exacta:
        {
            "quality_score": numero_del_1_al_100,
            "coherence_check": {
                "segment_hotel_match": true/false,
                "tone_consistency": true/false,
                "timing_logic": true/false,
                "cta_relevance": true/false
            },
            "strengths": ["fortaleza1", "fortaleza2"],
            "improvements": ["mejora1", "mejora2"],
            "final_verdict": "aprobada|aprobada con cambios|rechazada",
            "summary": "resumen ejecutivo en 2-3 frases"
        }

        Se critico pero justo. Responde SOLO el JSON, sin markdown ni explicaciones.
        PROMPT;

        return $this->client->complete($prompt, 'auditor');
    }

    /**
     * Re-runs the Creativo agent for a given follow-up attempt, escalating
     * the intensity one notch per attempt (capped at 5). Reads the previous
     * creative and any prior follow-up variants to avoid repetition, and
     * returns the new creative array (same JSON schema as the original).
     *
     * The caller is responsible for persisting the result into
     * `campaigns.followup_variants`.
     *
     * @return array<string, mixed>
     */
    public function regenerateForFollowup(Campaign $campaign, int $attempt): array
    {
        if ($attempt < 2 || $attempt > 5) {
            throw new \InvalidArgumentException('Follow-up attempt must be between 2 and 5.');
        }

        $baseAggressiveness = (int) ($campaign->aggressiveness ?? 2);
        $basePersuasionPatterns = (int) ($campaign->persuasion_patterns ?? 2);

        $this->aggressiveness = min(5, $baseAggressiveness + $attempt - 1);
        $this->persuasionPatterns = min(5, $basePersuasionPatterns + $attempt - 1);

        return $this->runCreativeFollowup($campaign, $attempt);
    }

    /**
     * @return array<string, mixed>
     */
    private function runCreativeFollowup(Campaign $campaign, int $attempt): array
    {
        $strategy = $campaign->strategy ?? [];
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $intensity = $this->intensityGuidance();
        $designGuide = $this->creativeDesignGuide($campaign);
        $brandContext = $this->buildBrandContext($campaign);
        $imageContext = $this->buildImageBankContext($campaign);

        $previousSubjects = [];
        if (! empty($campaign->creative['subject_line'] ?? null)) {
            $previousSubjects[] = '#1: "'.$campaign->creative['subject_line'].'"';
        }
        foreach (($campaign->followup_variants ?? []) as $n => $variant) {
            if (! empty($variant['subject_line'] ?? null)) {
                $previousSubjects[] = '#'.$n.': "'.$variant['subject_line'].'"';
            }
        }
        $previousSubjectsList = $previousSubjects === []
            ? '(ninguno)'
            : implode("\n        ", $previousSubjects);

        $objective = $campaign->objective;
        $hotelName = $strategy['recommended_hotel']['name'] ?? 'el hotel destacado';
        $hotelCity = $strategy['recommended_hotel']['city'] ?? '';

        $prompt = <<<PROMPT
        Eres el Agente Creativo de Roomie generando un EMAIL DE SEGUIMIENTO (intento #{$attempt}).
        El cliente no ha reservado aún tras los intentos anteriores.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA ORIGINAL:
        {$strategyJson}

        ASUNTOS YA ENVIADOS A ESTE CLIENTE (no repitas ninguno):
            {$previousSubjectsList}

        {$brandContext}

        {$intensity}

        {$designGuide}

        {$imageContext}

        REGLAS DEL SEGUIMIENTO:
        - Reconoce implícitamente que ya se contactó ("Te escribimos de nuevo", "Sigue disponible...").
        - Este es el intento #{$attempt}, la urgencia y la presión DEBEN subir un escalón claro respecto al intento anterior.
        - Si el intento es 4 o 5, introduce exclusividad explícita o "última oportunidad" en el body.
        - Mantén el mismo hotel destacado ({$hotelName}, {$hotelCity}) y la misma promesa de valor.
        - No repitas el subject_line ni el headline de intentos anteriores.

        Responde en JSON con la misma estructura EXACTA que el creativo original (subject_line, preview_text, headline, body_html, cta_text, cta_url_slug, alt_formats, visual_direction). El body_html sigue las DIRECTRICES DE DISEÑO de arriba al pie de la letra.

        Responde SOLO el JSON, sin markdown ni explicaciones.
        PROMPT;

        return $this->client->complete($prompt, 'creative-followup');
    }

    /**
     * Regenerate the creative in response to a free-form refinement prompt
     * from the user. The LLM receives the full campaign context (objective,
     * strategy, current creative) plus the user's instruction, and returns
     * a new creative JSON with only the requested changes applied.
     *
     * @return array<string, mixed>
     */
    public function refineCreative(Campaign $campaign, string $refinementPrompt): array
    {
        $this->aggressiveness = (int) ($campaign->aggressiveness ?? 2);
        $this->persuasionPatterns = (int) ($campaign->persuasion_patterns ?? 2);

        $strategy = $campaign->strategy ?? [];
        $currentCreative = $campaign->creative ?? [];
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $creativeJson = json_encode($currentCreative, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $intensity = $this->intensityGuidance();
        $designGuide = $this->creativeDesignGuide($campaign);
        $brandContext = $this->buildBrandContext($campaign);
        $imageContext = $this->buildImageBankContext($campaign);
        $objective = $campaign->objective;
        $safePrompt = trim($refinementPrompt);

        $prompt = <<<PROMPT
        Eres el Agente Creativo de Roomie. El usuario ya tiene un email generado y te pide un AJUSTE concreto. Tu tarea es aplicar ese ajuste respetando todo el contexto original de la campaña.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA ORIGINAL:
        {$strategyJson}

        CREATIVE ACTUAL (el que el usuario ya ve en la página):
        {$creativeJson}

        INSTRUCCIÓN DEL USUARIO (aplica solo esto, preservando todo lo demás):
        """
        {$safePrompt}
        """

        {$brandContext}

        {$intensity}

        {$designGuide}

        {$imageContext}

        REGLAS DEL AJUSTE:
        - Aplica la instrucción del usuario literalmente. Si pide "asunto más corto", acorta SOLO el asunto. Si pide "añade un pull-quote sobre el atardecer", añade ese pull-quote sin tocar el resto.
        - TODO lo que el usuario NO ha mencionado debe quedarse igual o muy parecido. No reescribas el email entero sin razón.
        - Mantén el mismo hotel, la misma ciudad, la misma promesa de valor, el mismo key_message, a menos que el usuario pida cambiarlos explícitamente.
        - Sigue las DIRECTRICES DE DISEÑO del body_html al pie de la letra — el resultado debe seguir siendo email-safe con inline styles y los patrones visuales permitidos.
        - Si el ajuste del usuario entra en conflicto con las directrices (p.ej. "usa color verde" cuando verde no está en la paleta), prioriza las directrices y explica brevemente por qué en el campo `visual_direction`.

        Responde SOLO con el JSON completo del creative actualizado (subject_line, preview_text, headline, body_html, cta_text, cta_url_slug, alt_formats, visual_direction). Nada de markdown, nada de explicaciones fuera del JSON.
        PROMPT;

        return $this->client->complete($prompt, 'creative-refine');
    }

    private function buildBrandContext(Campaign $campaign): string
    {
        $brand = $campaign->user?->brandSetting;
        if (! $brand || ! $brand->brand_name) {
            return '';
        }

        $parts = ['IDENTIDAD DE MARCA:'];
        $parts[] = "- Nombre: {$brand->brand_name}";

        if ($brand->primary_color) {
            $parts[] = "- Color primario: {$brand->primary_color}";
        }
        if ($brand->secondary_color) {
            $parts[] = "- Color secundario: {$brand->secondary_color}";
        }
        if ($brand->voice_description) {
            $parts[] = "- Voz de marca: {$brand->voice_description}";
        }
        if ($brand->contact_email || $brand->contact_phone || $brand->contact_website) {
            $contact = array_filter([
                $brand->contact_email ? "email: {$brand->contact_email}" : null,
                $brand->contact_phone ? "tel: {$brand->contact_phone}" : null,
                $brand->contact_website ? "web: {$brand->contact_website}" : null,
            ]);
            $parts[] = '- Contacto: '.implode(', ', $contact);
        }
        if ($brand->social_links) {
            $social = collect($brand->social_links)
                ->map(fn ($url, $platform) => "{$platform}: {$url}")
                ->implode(', ');
            $parts[] = "- Redes: {$social}";
        }

        return implode("\n        ", $parts);
    }

    private function buildImageBankContext(Campaign $campaign): string
    {
        $images = $campaign->user?->bankImages()
            ->select('id', 'title', 'alt_text', 'category', 'tags', 'width', 'height')
            ->get();

        if (! $images || $images->isEmpty()) {
            return '';
        }

        $listing = $images->map(fn ($img) => "- [{$img->id}] \"{$img->title}\""
            .($img->category ? " ({$img->category})" : '')
            .($img->width ? " {$img->width}x{$img->height}px" : '')
            .($img->alt_text ? " alt=\"{$img->alt_text}\"" : '')
            .($img->tags ? ' tags: '.implode(', ', $img->tags) : '')
        )->implode("\n        ");

        return <<<CTX
        BANCO DE IMÁGENES DISPONIBLE:
        El usuario tiene las siguientes imágenes que puedes usar en el body_html.
        Para incluir una imagen, usa exactamente este placeholder: {{image:ID}}
        El sistema lo reemplazará por la URL real al renderizar.

        {$listing}

        REGLAS DE USO DE IMÁGENES:
        - Máximo 2 imágenes por email (para no saturar).
        - Cada imagen debe ir como: <img src="{{image:ID}}" alt="alt text" style="display:block;max-width:100%;height:auto;margin:0 0 20px;border-radius:8px;">
        - Si ninguna imagen es relevante para el contenido, NO incluyas ninguna. Es preferible no incluir imágenes a meter una irrelevante.
        - Las imágenes van DENTRO del body_html, entre los párrafos, nunca como hero.
        CTX;
    }

    private function intensityGuidance(): string
    {
        $aggressivenessLevels = [
            0 => 'Puramente informativa. Describe el hotel y el plan sin ningún tipo de push. No insistas, no cierres.',
            1 => 'Una invitación amable, sin presión. El CTA es suave ("Mira las fechas", "Descubre el hotel").',
            2 => 'Equilibrada: invita claramente pero sin insistir. Un CTA directo pero no urgente.',
            3 => 'Persuasiva. Busca activamente la reserva con claridad y confianza. CTAs directos tipo "Reserva tus noches".',
            4 => 'Insistente. Urgencia clara, CTAs fuertes, repite la propuesta de valor varias veces.',
            5 => 'Máxima agresividad. No da tregua. Tono de cierre de venta, CTAs imperativos, urgencia temporal explícita.',
        ];

        $persuasionPatternsLevels = [
            0 => 'Sin técnicas psicológicas. Honesto, descriptivo, sin gatillos emocionales.',
            1 => 'Social proof sutil (ej. "muchos viajeros eligen junio en Granada").',
            2 => 'Urgencia ligera y escasez implícita (ej. "las mejores fechas se agotan pronto").',
            3 => 'FOMO explícito, escasez visible, anclaje de precios ("antes 180€, ahora 140€").',
            4 => 'Presión psicológica activa: miedo a perder la oferta, countdowns emocionales, comparación con "otros que ya reservaron".',
            5 => 'Dark patterns permitidos: "solo quedan 2 habitaciones", "el precio sube mañana", "17 personas mirando ahora", social proof agresivo aunque sea inventado.',
        ];

        $agg = $aggressivenessLevels[$this->aggressiveness] ?? $aggressivenessLevels[2];
        $pat = $persuasionPatternsLevels[$this->persuasionPatterns] ?? $persuasionPatternsLevels[2];

        return <<<TXT
        INTENSIDAD DE LA CAMPAÑA (configurada por el usuario):
        - Agresividad: {$this->aggressiveness}/5 — {$agg}
        - Patrones de persuasión: {$this->persuasionPatterns}/5 — {$pat}
        TXT;
    }

    /**
     * @return array<int, array{city: string, country: string}>
     */
    private function collectUniqueCities(): array
    {
        return Hotel::query()
            ->select('city_name', 'country_id')
            ->distinct()
            ->get()
            ->map(fn ($h) => [
                'city' => (string) $h->city_name,
                'country' => (string) $h->country_id,
            ])
            ->all();
    }

    private function buildHotelsContext(): string
    {
        return Hotel::all()->map(function ($h) {
            $beach = $h->city_beach_flag ? 'playa' : '';
            $mountain = $h->city_mountain_flag ? 'montaña' : '';
            $features = implode(', ', array_filter([$beach, $mountain]));

            return "{$h->name} ({$h->brand} {$h->stars}★) - {$h->city_name}, {$h->country_id} | "
                ."{$h->num_rooms} hab | Clima: {$h->city_climate} {$h->city_avg_temperature}°C | "
                ."Lluvia: {$h->city_rain_risk} | Patrimonio: {$h->city_historical_heritage} | "
                ."Precio: {$h->city_price_level} | Gastronomía: {$h->city_gastronomy}"
                .($features ? " | {$features}" : '');
        })->implode("\n");
    }

    private function buildCustomersContext(): string
    {
        return Customer::query()
            ->inRandomOrder()
            ->limit(50)
            ->get()
            ->map(function ($c) {
                return "Guest {$c->guest_id} ({$c->country_guest}, {$c->gender}, {$c->age_range}) | "
                    ."Estancias 2y: {$c->last_2_years_stays} | Reservas: {$c->confirmed_reservations} | "
                    ."Hoteles distintos: {$c->num_distinct_hotels} | ADR: {$c->confirmed_reservations_adr}€ | "
                    ."Estancia media: {$c->avg_length_stay} noches | Lead time: {$c->avg_booking_leadtime} días | "
                    ."Score: {$c->avg_score} | Último hotel: {$c->hotel_external_id} ({$c->checkin_date->format('Y-m-d')})";
            })->implode("\n");
    }
}
