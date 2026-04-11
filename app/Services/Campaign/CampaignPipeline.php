<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Hotel;
use App\Services\LLM\LlmClient;
use Illuminate\Support\Facades\Log;

class CampaignPipeline
{
    public function __construct(
        private LlmClient $client,
    ) {}

    public function run(Campaign $campaign): void
    {
        $campaign->update(['status' => 'processing']);

        try {
            $hotelsContext = $this->buildHotelsContext();
            $customersContext = $this->buildCustomersContext();

            $analysis = $this->runAnalyst($campaign->objective, $hotelsContext, $customersContext);
            $campaign->update(['analysis' => $analysis]);

            $strategy = $this->runStrategist($campaign->objective, $analysis, $hotelsContext);
            $campaign->update(['strategy' => $strategy]);

            $creative = $this->runCreative($campaign->objective, $strategy);
            $campaign->update(['creative' => $creative]);

            $audit = $this->runAuditor($campaign->objective, $strategy, $creative);
            $campaign->update([
                'audit' => $audit,
                'quality_score' => $audit['quality_score'] ?? null,
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            Log::error('Campaign pipeline failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            $campaign->update(['status' => 'failed']);

            throw $e;
        }
    }

    private function runAnalyst(string $objective, string $hotels, string $customers): array
    {
        $prompt = <<<PROMPT
        Eres el Agente Analista de campañas de marketing hotelero para Eurostars Hotel Company.

        OBJETIVO DE NEGOCIO: {$objective}

        DATOS DE HOTELES:
        {$hotels}

        DATOS DE CLIENTES (muestra):
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

    private function runStrategist(string $objective, array $analysis, string $hotels): array
    {
        $analysisJson = json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
        Eres el Agente Estratega de campañas de marketing hotelero para Eurostars Hotel Company.

        OBJETIVO DE NEGOCIO: {$objective}

        ANÁLISIS PREVIO:
        {$analysisJson}

        HOTELES DISPONIBLES:
        {$hotels}

        Diseña la estrategia de campaña. Responde en JSON con esta estructura exacta:
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

    private function runCreative(string $objective, array $strategy): array
    {
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
        Eres el Agente Creativo de campañas de marketing hotelero para Eurostars Hotel Company.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA:
        {$strategyJson}

        Genera el contenido creativo de la campaña. Responde en JSON con esta estructura exacta:
        {
            "subject_line": "asunto del email",
            "preview_text": "texto de preview del email (max 90 chars)",
            "headline": "titulo principal del email",
            "body_html": "contenido HTML del cuerpo del email con estilos inline, diseño profesional hotelero, maximo 300 palabras. Usa colores elegantes (#1a1a2e, #e2d1c3, #16213e). Incluye un CTA claro.",
            "cta_text": "texto del boton CTA",
            "cta_url_slug": "slug para la URL de destino",
            "alt_formats": {
                "push_notification": "texto de push (max 100 chars)",
                "sms": "texto de SMS (max 160 chars)",
                "social_caption": "caption para redes sociales"
            },
            "visual_direction": "descripcion de la direccion visual/fotografica sugerida"
        }

        El tono debe ser: {$strategy['tone']}
        El mensaje clave es: {$strategy['key_message']}
        Hotel destacado: {$strategy['recommended_hotel']['name']} en {$strategy['recommended_hotel']['city']}

        Responde SOLO el JSON, sin markdown ni explicaciones. El body_html debe ser HTML valido con estilos inline.
        PROMPT;

        return $this->client->complete($prompt, 'creative');
    }

    private function runAuditor(string $objective, array $strategy, array $creative): array
    {
        $strategyJson = json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $creativeJson = json_encode($creative, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
        Eres el Agente Auditor de campañas de marketing hotelero para Eurostars Hotel Company.
        Tu trabajo es revisar la coherencia y calidad de una campaña antes de lanzarla.

        OBJETIVO DE NEGOCIO: {$objective}

        ESTRATEGIA:
        {$strategyJson}

        CONTENIDO CREATIVO:
        {$creativeJson}

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

    private function buildHotelsContext(): string
    {
        return Hotel::all()->map(function ($h) {
            $beach = $h->city_beach_flag ? 'playa' : '';
            $mountain = $h->city_mountain_flag ? 'montaña' : '';
            $features = implode(', ', array_filter([$beach, $mountain]));

            return "{$h->name} ({$h->brand} {$h->stars}★) - {$h->city_name}, {$h->country_id} | "
                . "{$h->num_rooms} hab | Clima: {$h->city_climate} {$h->city_avg_temperature}°C | "
                . "Lluvia: {$h->city_rain_risk} | Patrimonio: {$h->city_historical_heritage} | "
                . "Precio: {$h->city_price_level} | Gastronomía: {$h->city_gastronomy}"
                . ($features ? " | {$features}" : '');
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
                    . "Estancias 2y: {$c->last_2_years_stays} | Reservas: {$c->confirmed_reservations} | "
                    . "Hoteles distintos: {$c->num_distinct_hotels} | ADR: {$c->confirmed_reservations_adr}€ | "
                    . "Estancia media: {$c->avg_length_stay} noches | Lead time: {$c->avg_booking_leadtime} días | "
                    . "Score: {$c->avg_score} | Último hotel: {$c->hotel_external_id} ({$c->checkin_date->format('Y-m-d')})";
            })->implode("\n");
    }
}
