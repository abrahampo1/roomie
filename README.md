# Roomie - Make Me Want to Travel

Plataforma de generación de campañas de marketing hotelero impulsada por IA. Utiliza un pipeline de 4 agentes inteligentes (Claude) para crear campañas de marketing hiperpersonalizadas para cadenas hoteleras.

Desarrollado para **Impacthon 2026** — reto de Eurostars Hotel Company.

## Arquitectura del Pipeline

El sistema orquesta 4 agentes de IA en secuencia:

1. **Analyst** — Segmenta clientes según datos del hotel y comportamiento, identifica factores estacionales y recomienda segmentos objetivo.
2. **Strategist** — Diseña la estrategia de campaña: segmentos, hoteles recomendados, timing, canal de comunicación, tono y mensajes clave.
3. **Creative** — Genera los activos creativos: asunto de email, cuerpo HTML con estilos inline, CTAs, y formatos alternativos (push, SMS, redes sociales).
4. **Auditor** — Revisa la coherencia de la campaña, asigna puntuación de calidad (1-100) y emite veredicto de aprobación.

## Tech Stack

- **Backend:** Laravel 13 · PHP 8.4
- **Frontend:** Blade · Tailwind CSS 4 · Vite 8
- **IA:** Anthropic Claude API (claude-sonnet-4-20250514)
- **Base de datos:** SQLite (configurable)
- **Testing:** Pest 4
- **Cola:** Database driver (procesamiento asíncrono de campañas)

## Requisitos

- PHP >= 8.3
- Composer
- Node.js y npm
- Una API key de [Anthropic](https://console.anthropic.com/)

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/abrahampo1/roomie.git
cd roomie

# Setup completo (dependencias, migraciones, build)
composer setup

# Configurar la API key de Anthropic en el archivo .env
# ANTHROPIC_API_KEY=tu_api_key
```

## Uso

```bash
# Iniciar el servidor de desarrollo (servidor, cola, logs y Vite)
composer dev
```

Esto ejecuta concurrentemente:
- `php artisan serve` — Servidor web
- `php artisan queue:listen` — Procesador de cola para campañas
- `php artisan pail` — Streaming de logs
- `npm run dev` — Vite con HMR

## Tests

```bash
composer test
```

## Estructura de Datos

### Hotels
Datos de hoteles incluyendo ubicación, categoría (estrellas), marca y metadatos de la ciudad (clima, temperatura, playa, montaña, gastronomía, etc.).

### Customers
Perfiles de clientes con historial de reservas, métricas de comportamiento (estancias en últimos 2 años, ADR, duración promedio, lead time de reserva) y puntuación promedio.

### Campaigns
Campañas generadas con su objetivo de negocio, estado del pipeline (`pending` / `processing` / `completed` / `failed`), resultados de cada agente (analysis, strategy, creative, audit) y puntuación de calidad.

## Licencia

MIT
