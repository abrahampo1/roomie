# Roomie

<p align="center">
  <img src="public/images/brand/LogoRoomie.svg" alt="Roomie" width="280">
</p>

<p align="center">
  Campanas de marketing hotelero hiper-personalizadas, disenadas por 4 agentes de IA.
</p>

---

**Roomie** es un generador de campanas de marketing hotelero impulsado por IA. El usuario introduce un objetivo de negocio y un pipeline de 4 agentes analiza datos de hoteles y clientes, define estrategia, genera contenido creativo y audita la calidad del resultado.

Proyecto desarrollado para el reto de **Eurostars Hotel Company** en el **Impacthon 2026**.

## Pipeline de 4 agentes

| Agente | Funcion |
|--------|---------|
| **Analista** | Segmenta clientes y extrae insights de mercado a partir de los datos |
| **Estratega** | Define publico objetivo, hotel, timing y canal optimo |
| **Creativo** | Genera copy de email, asunto, CTA y formatos alternativos (push, SMS, social) |
| **Auditor** | Revisa coherencia entre las fases y asigna un score de calidad (0-100) |

Cada agente llama a la API de Claude (Anthropic) de forma secuencial, pasando su output como contexto al siguiente.

## Tech stack

- **Backend:** PHP 8.4 / Laravel 13
- **Frontend:** Blade + Tailwind CSS v4 + Vite
- **IA:** API de Anthropic (Claude Sonnet 4)
- **Base de datos:** SQLite (por defecto)
- **Cola:** Database driver (para el pipeline asincrono)
- **Testing:** Pest v4

## Instalacion

```bash
git clone <repo-url> roomie
cd roomie

composer install
cp .env.example .env
php artisan key:generate
```

Roomie usa **bring your own key**: cada usuario introduce su propia clave al crear una campaña. Soporta Anthropic Claude, Google Gemini, OpenAI, DeepSeek y cualquier endpoint compatible con la API de OpenAI (Together, Groq, Fireworks, un modelo local, etc.) introduciendo base URL y modelo. La clave se cifra, se usa para esa ejecución del pipeline y se borra de la base de datos en cuanto el job termina (éxito o fallo). El frontend la guarda en `localStorage` para no tener que pegarla cada vez.

No hace falta configurar ninguna clave en `.env`.

Prepara la base de datos y carga los datos de ejemplo:

```bash
php artisan migrate
php artisan db:seed
```

Compila los assets del frontend:

```bash
npm install
npm run build
```

## Desarrollo

```bash
composer run dev
```

Esto arranca concurrentemente el servidor de Laravel, el worker de colas, los logs y Vite.

## Testing

```bash
composer test
```

## Estructura clave

```
app/
  Http/Controllers/CampaignController.php   # CRUD de campanas
  Jobs/RunCampaignPipeline.php               # Job asincrono del pipeline
  Models/{Hotel,Customer,Campaign}.php       # Modelos Eloquent
  Services/Campaign/CampaignPipeline.php     # Logica de los 4 agentes
database/
  seeders/                                   # Carga hotel_data.csv y customer_data_200.csv
docs/
  hotel_data.csv                             # Dataset de hoteles
  customer_data_200.csv                      # Dataset de clientes (200 registros)
resources/views/
  welcome.blade.php                          # Landing page
  campaigns/{index,create,show}.blade.php    # Vistas de campanas
```

## Rutas

| Metodo | URI | Descripcion |
|--------|-----|-------------|
| GET | `/` | Landing page |
| GET | `/campaigns` | Listado de campanas |
| GET | `/campaigns/create` | Formulario de nueva campana |
| POST | `/campaigns` | Lanza el pipeline |
| GET | `/campaigns/{id}` | Detalle de campana |
| GET | `/campaigns/{id}/status` | Estado JSON (polling) |

## Licencia

Proyecto academico — Impacthon 2026.
