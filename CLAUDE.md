# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project context

Roomie is a hotel marketing campaign generator built for the Eurostars Hotel Company challenge at Impacthon 2026. A user types a business objective ("raise off-season occupancy at Eurostars Torre Sevilla", "reactivate guests who haven't booked in 6 months") and a pipeline of four LLM agents runs sequentially to produce a ready-to-send email campaign with strategy, creative and a quality audit.

The whole stack is Spanish-first. Copy, UI labels and the prompt instructions all live in Spanish — preserve that voice when editing text.

## Commands

- `composer run dev` — starts `php artisan serve`, `queue:listen`, `pail` (log tail) and `npm run dev` concurrently. Default way to develop.
- `composer test` — clears config cache and runs Pest. `php artisan test --compact --filter=testName` for a single test.
- `php artisan migrate:fresh --seed` — nuke and reseed. Needed when playing with the customer/hotel datasets.
- `php artisan db:seed --class=CustomerSeeder` — reseed just customers.
- `vendor/bin/pint --dirty --format agent` — run after touching any PHP file (mandated by the embedded Boost rules below).
- `npm run build` — production asset build when the dev server isn't running.

## Architecture — the four-agent pipeline

The core domain is a single linear pipeline. One request goes through five files, in order:

1. **`app/Http/Controllers/CampaignController.php`** — `store()` validates the form (objective + LLM provider + API key + optional custom URL/model), creates a `Campaign` row with `status=pending`, and dispatches `RunCampaignPipeline`.
2. **`app/Jobs/RunCampaignPipeline.php`** — builds an `LlmClient` via `LlmClientFactory::make(...)` and hands it to `CampaignPipeline`. A `finally` block wipes `api_key` from the campaign row regardless of outcome — **this is load-bearing, don't remove it** (see BYOK section).
3. **`app/Services/Campaign/CampaignPipeline.php`** — runs four private methods (`runAnalyst`, `runStrategist`, `runCreative`, `runAuditor`) in order. Each one writes its result to the campaign (`analysis`, `strategy`, `creative`, `audit`) **before** the next one runs, so the `/campaigns/{id}/status` polling endpoint can show progressive completion. The strategist, creative and auditor each receive the *output* of the previous agent as context. Hotels and a random 50-customer sample are built once at the top of `run()` and reused.
4. **`app/Services/LLM/LlmClient.php`** + implementations — the abstraction the pipeline actually talks to. See next section.
5. **`app/Models/Campaign.php`** — `analysis`, `strategy`, `creative`, `audit` are `array` casts backed by JSON columns. `api_key` is an `encrypted` cast.

Prompts are inline heredocs in `CampaignPipeline`. They demand `Responde SOLO el JSON` at the end because the two JSON-mode-capable clients (Anthropic doesn't have one, Google and OpenAI-compatible do) still fall through `JsonExtractor::fromText()` which has a regex fallback (`/\{[\s\S]*\}/`) for when models wrap the answer in prose.

Status polling lives in `campaigns/show.blade.php` — a `@push('scripts')` block polls `/campaigns/{id}/status` every 3s and reloads on `completed`/`failed`.

## LLM provider abstraction (BYOK — critical)

**Roomie has no global LLM API key and should not grow one.** The user supplies their key per campaign via the form. The key is encrypted on `campaigns.api_key` (encrypted cast), used by the job, then nulled in the job's `finally` block.

Supported providers, all routed through `app/Services/LLM/LlmClientFactory::make()`:

| Provider | Client | Model | Notes |
|---|---|---|---|
| `anthropic` | `AnthropicClient` | `claude-sonnet-4-20250514` | Messages API, no JSON mode |
| `google` | `GoogleClient` | `gemini-2.0-flash` | Uses `responseMimeType: application/json` |
| `openai` | `OpenAiCompatibleClient` | `gpt-4o-mini` | OpenAI chat completions, `response_format: json_object` |
| `deepseek` | `OpenAiCompatibleClient` | `deepseek-chat` | Same as OpenAI |
| `custom` | `OpenAiCompatibleClient` | user-supplied | Requires `api_base_url` + `api_model` from the form, JSON enforcement off (not every OpenAI-compatible server supports it) |

`OpenAiCompatibleClient` handles `openai`, `deepseek` and `custom` — the three differ only in base URL, model, and the `enforceJson` flag. When adding a new provider, prefer extending the factory + reusing `OpenAiCompatibleClient` if the server speaks OpenAI chat completions.

The form UI (`campaigns/create.blade.php`) caches keys in `localStorage` keyed by `roomie:llm-key:{provider}` so users don't re-paste across sessions. Custom also caches URL and model separately. The keys never leave the browser except when submitted with the form.

## Key gotchas

- **Design language is restrained editorial, not brutalist.** The user explicitly pushed back on hard offset shadows, outlined display text, marquee strips, `/ slash` prefixed labels, `01/02/03` numbered badges and bento grids — those all read as AI-generated. The current look uses Fredoka for display, Inter body, JetBrains Mono for small captions, a single `text-copper` accent per section, `definition list` semantics and generous whitespace. Match that voice.
- **Mobile inputs must be `text-base` (16px)** or iOS Safari zooms on focus. Applies to all form fields. The key/URL/model inputs also need `autocapitalize="none" autocorrect="off" spellcheck="false"` — iOS will otherwise corrupt them.
- **Customers seeder has a composite `(guest_id, reservation_id)` unique**, not a standalone unique on `guest_id`. The CSV contains multiple reservations per guest. Migration `2026_04_11_000003_fix_customers_guest_id_unique.php` replaced the original unique — don't re-introduce the standalone one.
- **The hotel/customer context passed to the LLM is built fresh every run** from `Hotel::all()` and `Customer::inRandomOrder()->limit(50)`. Keep an eye on this if either table grows large — the whole hotels table is currently serialized into every prompt.
- **Market intelligence is evidence-only, not persuasion.** `app/Services/MarketIntelligence/MarketIntelligenceService.php` hits Open-Meteo (keyless, cached 6h) and INE tempus3 (EOH table `2074` + FRONTUR table `24304`, cached 7d) and returns a compact `CONTEXTO DE MERCADO` blob. That blob is injected **only** into the Analista and Estratega prompts — never into Creativo or Auditor. Market facts reach the creative copy indirectly via `strategy.timing.reason`. Degradation is total: if every external call fails, `getMarketContext()` returns `''` and the `CONTEXTO DE MERCADO:` header is elided entirely so the prompt has no dangling section. Don't add AEMET or a paid weather provider — Open-Meteo's keyless geocoding is the only thing that makes the "hotel has no lat/lon" problem disappear without a second vendor.

- **Send + tracking + AI follow-ups is a full post-pipeline subsystem.** Once a campaign is `completed` the user can click "Enviar" on the show page. `CampaignSendController::send()` calls `RecipientSelector` (heuristic, no LLM) to rank customers by hotel match + ADR band + score, snapshots them into `campaign_recipients`, and dispatches `SendCampaignJob` which chunks and queues `SendCampaignEmailJob` per 20 recipients with staggered delays. Each recipient gets a `CampaignEmail` mailable with the body HTML rewritten through `LinkRewriter` (DOMDocument, not regex) so every `<a href>` goes through our click redirect, a 1×1 open pixel, and a `List-Unsubscribe` header for RFC 8058 one-click. Stats live in `CampaignStatsService` (one aggregate query) and the show page's `_stats_section` partial refreshes via `fetch` + `innerHTML` swap.

- **Tracking routes are PUBLIC and have carefully chosen middleware.** `/t/o/{recipient}/{token}` (open pixel) deliberately skips `signed` middleware because Gmail's image proxy strips query-string signatures — the token alone is the integrity check and the controller always responds `200` with a GIF even on mismatch. `/t/c/...`, `/t/u/...` GET and `/t/u/...` POST all use `signed`. The POST unsubscribe route is excluded from CSRF in `bootstrap/app.php` so `List-Unsubscribe-Post: List-Unsubscribe=One-Click` from Gmail works; the signed URL + per-recipient token cover the attack surface.

- **BYOK retention for autonomous follow-ups is opt-in, bounded and self-terminating.** The user's LLM key is still wiped in `RunCampaignPipeline::handle()`'s `finally` for the initial run — that invariant stands. To enable follow-ups, the user re-pastes the key on the send drawer and ticks an explicit checkbox; the key goes back onto `campaigns.api_key` (encrypted cast), `api_key_retained_for_followups = true`, and `api_key_retention_expires_at = now() + ROOMIE_FOLLOWUP_MAX_RETENTION_DAYS` (default 14 days, hard cap). Five independent conditions wipe the key: (1) user clicks "Detener secuencia", (2) `attempts_sent >= followup_max_attempts` for every eligible recipient, (3) all recipients terminal (converted/unsubscribed/bounced), (4) `api_key_retention_expires_at < now()` picked up by the hourly `WipeExpiredCampaignKeysCommand`, (5) the `ProcessCampaignFollowupsCommand` detects "no more work" at the end of a pass. Do not add more retention paths — more is always worse for BYOK.

- **Follow-up escalation reuses `intensityGuidance()`.** `CampaignPipeline::regenerateForFollowup()` mutates `$this->aggressiveness` and `$this->manipulation` to `min(5, original + attempt - 1)` before calling `runCreativeFollowup()`, which reuses the same intensity helper as the initial run. **One creative per attempt, not per recipient**: the generated creative is cached in `campaigns.followup_variants[attempt]` (JSON) and reused for every recipient who lands on that attempt — saves ~90% of LLM calls in the loop. **The Auditor is NOT re-run** on follow-ups (25% LLM cost savings, coherence already validated on the original).

- **`ROOMIE_ALLOW_REAL_SENDS` is the load-bearing safety gate.** `CampaignSender::sendOne()` forces `Mail::mailer('log')` when this env flag is false, regardless of `MAIL_MAILER`. Combined with the `@example.invalid` backfill on every seeded customer (RFC 2606 — never resolves to a real mailbox), the default mode is double-safe. To send for real: set `ROOMIE_ALLOW_REAL_SENDS=true`, configure `MAIL_MAILER=smtp` (or similar), AND load real email addresses. Flag documented in `.env.example`.

- **Scheduled commands run via `php artisan schedule:work`** during dev or a real cron in prod. `campaigns:process-followups` runs every 15 minutes with `withoutOverlapping(10)` so two ticks don't race on the same campaign. `campaigns:wipe-expired-keys` runs hourly as the retention-expiration safety net.

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
