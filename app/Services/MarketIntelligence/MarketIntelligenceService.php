<?php

namespace App\Services\MarketIntelligence;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Builds a compact "INTELIGENCIA DE MERCADO" text blob injected into the
 * Analista and Estratega prompts. Pulls live weather (Open-Meteo) and Spanish
 * tourism statistics (INE tempus3 — EOH + FRONTUR). Every external call is
 * cached; any failure degrades gracefully and the caller sees an empty string
 * instead of an exception.
 */
class MarketIntelligenceService
{
    private const EOH_TABLE_URL = 'https://servicios.ine.es/wstempus/js/ES/DATOS_TABLA/2074?nult=2&tip=AM';

    private const FRONTUR_TABLE_URL = 'https://servicios.ine.es/wstempus/js/ES/DATOS_TABLA/24304?nult=1';

    private const OPEN_METEO_GEO_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    private const OPEN_METEO_FORECAST_URL = 'https://api.open-meteo.com/v1/forecast';

    /** City names as stored in the catalog mapped to what Open-Meteo expects. */
    private const CITY_ALIASES = [
        'MADRID' => 'Madrid',
        'SEVILLA' => 'Seville',
        'GRANADA' => 'Granada',
        'EL GROVE' => 'O Grove',
        'LISBOA' => 'Lisbon',
        'OPORTO' => 'Porto',
        'ROMA' => 'Rome',
    ];

    /** Spanish cities → Comunidad Autónoma label used by INE EOH. */
    private const ES_CITY_TO_CCAA = [
        'MADRID' => 'Comunidad de Madrid',
        'SEVILLA' => 'Andalucía',
        'GRANADA' => 'Andalucía',
        'EL GROVE' => 'Galicia',
    ];

    /** Minimal weathercode → Spanish label mapping (WMO codes). */
    private const WEATHER_CODES = [
        0 => 'despejado',
        1 => 'mayormente soleado',
        2 => 'parcialmente nublado',
        3 => 'nublado',
        45 => 'niebla',
        48 => 'niebla',
        51 => 'llovizna ligera',
        53 => 'llovizna',
        55 => 'llovizna intensa',
        61 => 'lluvia ligera',
        63 => 'lluvia',
        65 => 'lluvia intensa',
        71 => 'nieve ligera',
        73 => 'nieve',
        75 => 'nieve intensa',
        80 => 'chubascos',
        81 => 'chubascos fuertes',
        82 => 'chubascos muy fuertes',
        95 => 'tormenta',
        96 => 'tormenta con granizo',
        99 => 'tormenta fuerte con granizo',
    ];

    /**
     * Build the full INTELIGENCIA DE MERCADO text blob.
     *
     * @param  array<int, array{city: string, country: string}>  $cities
     */
    public function getMarketContext(array $cities): string
    {
        $weather = $this->safely(fn () => $this->getWeatherSnapshot($cities), 'weather');
        $eoh = $this->safely(fn () => $this->getEohByCcaa($cities), 'ine_eoh');
        $frontur = $this->safely(fn () => $this->getFronturTopOrigins(), 'ine_frontur');

        if (empty($weather) && empty($eoh) && empty($frontur)) {
            return '';
        }

        return $this->formatBlob($cities, $weather, $eoh, $frontur);
    }

    /**
     * Wrap a section fetcher so a single API failure doesn't poison the blob.
     *
     * @template T
     *
     * @param  callable(): T  $fetcher
     * @return T|array<never, never>
     */
    private function safely(callable $fetcher, string $section): array
    {
        try {
            return $fetcher() ?: [];
        } catch (Throwable $e) {
            Log::warning("MarketIntelligenceService[{$section}] failed: ".$e->getMessage());

            return [];
        }
    }

    /**
     * @param  array<int, array{city: string, country: string}>  $cities
     * @return array<string, string>  City display name → one-sentence summary.
     */
    private function getWeatherSnapshot(array $cities): array
    {
        $cityNames = collect($cities)->pluck('city')->unique()->sort()->values()->all();

        if (empty($cityNames)) {
            return [];
        }

        $cacheKey = 'market_intel:weather:v1:'.md5(implode('|', $cityNames));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($cityNames) {
            $coords = [];
            foreach ($cityNames as $cityName) {
                $geo = $this->geocodeCity($cityName);
                if ($geo !== null) {
                    $coords[$cityName] = $geo;
                }
            }

            if (empty($coords)) {
                return [];
            }

            $responses = Http::pool(function (Pool $pool) use ($coords) {
                $requests = [];
                foreach ($coords as $cityName => $geo) {
                    $requests[] = $pool->as($cityName)->timeout(5)->get(self::OPEN_METEO_FORECAST_URL, [
                        'latitude' => $geo['lat'],
                        'longitude' => $geo['lon'],
                        'daily' => 'temperature_2m_min,temperature_2m_max,precipitation_probability_max,weathercode',
                        'forecast_days' => 7,
                        'timezone' => 'auto',
                    ]);
                }

                return $requests;
            });

            $out = [];
            foreach ($responses as $cityName => $response) {
                if (! $response instanceof Response || ! $response->successful()) {
                    continue;
                }

                $summary = $this->summarizeDailyForecast($response->json('daily') ?? []);
                if ($summary !== null) {
                    $out[$cityName] = $summary;
                }
            }

            return $out;
        });
    }

    /**
     * @return array{lat: float, lon: float}|null
     */
    private function geocodeCity(string $cityName): ?array
    {
        $alias = self::CITY_ALIASES[$cityName] ?? $cityName;

        return Cache::rememberForever('market_intel:geo:v1:'.Str::slug($alias), function () use ($alias) {
            try {
                $response = Http::timeout(5)->get(self::OPEN_METEO_GEO_URL, [
                    'name' => $alias,
                    'count' => 1,
                    'language' => 'es',
                    'format' => 'json',
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $first = $response->json('results.0');
                if (! is_array($first) || ! isset($first['latitude'], $first['longitude'])) {
                    return null;
                }

                return [
                    'lat' => (float) $first['latitude'],
                    'lon' => (float) $first['longitude'],
                ];
            } catch (Throwable $e) {
                Log::warning("MarketIntelligenceService[geocode {$alias}] failed: ".$e->getMessage());

                return null;
            }
        });
    }

    /**
     * @param  array<string, mixed>  $daily
     */
    private function summarizeDailyForecast(array $daily): ?string
    {
        $minTemps = $daily['temperature_2m_min'] ?? null;
        $maxTemps = $daily['temperature_2m_max'] ?? null;
        $codes = $daily['weathercode'] ?? null;
        $precProb = $daily['precipitation_probability_max'] ?? null;

        if (! is_array($minTemps) || ! is_array($maxTemps) || ! is_array($codes) || empty($minTemps)) {
            return null;
        }

        $minOverall = (int) round(min($minTemps));
        $maxOverall = (int) round(max($maxTemps));

        $dominantCode = $this->pickDominantWeatherCode($codes);
        $label = self::WEATHER_CODES[$dominantCode] ?? 'condiciones variables';

        $rainyDays = 0;
        if (is_array($precProb)) {
            foreach ($precProb as $p) {
                if ((int) $p >= 50) {
                    $rainyDays++;
                }
            }
        }
        $rainSuffix = match (true) {
            $rainyDays === 0 => 'sin lluvia',
            $rainyDays === 1 => '1 día con lluvia',
            default => "{$rainyDays} días con lluvia",
        };

        return "{$minOverall}-{$maxOverall}°C, {$label}, {$rainSuffix}.";
    }

    /**
     * @param  array<int, int|float>  $codes
     */
    private function pickDominantWeatherCode(array $codes): int
    {
        $counts = [];
        foreach ($codes as $code) {
            $key = (int) $code;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        if (empty($counts)) {
            return 0;
        }
        arsort($counts);

        return (int) array_key_first($counts);
    }

    /**
     * @param  array<int, array{city: string, country: string}>  $cities
     * @return array<string, array{rate: float, delta: ?float, stay: ?float}>
     */
    private function getEohByCcaa(array $cities): array
    {
        $ccaaNeeded = collect($cities)
            ->filter(fn ($c) => strtoupper($c['country'] ?? '') === 'ES')
            ->map(fn ($c) => self::ES_CITY_TO_CCAA[strtoupper($c['city'])] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ccaaNeeded)) {
            return [];
        }

        $raw = Cache::remember('market_intel:ine:eoh:v1', now()->addDays(7), function () {
            $response = Http::timeout(5)->get(self::EOH_TABLE_URL);

            if (! $response->successful()) {
                return [];
            }

            return $response->json() ?? [];
        });

        if (! is_array($raw) || empty($raw)) {
            return [];
        }

        $occupancyByCcaa = [];
        $stayByCcaa = [];

        foreach ($raw as $series) {
            $metaNames = array_map(
                fn ($m) => is_array($m) ? ($m['Nombre'] ?? '') : '',
                $series['MetaData'] ?? []
            );

            $matchedCcaa = null;
            foreach ($metaNames as $name) {
                foreach ($ccaaNeeded as $candidate) {
                    if ($this->matchesLoose($name, $candidate)) {
                        $matchedCcaa = $candidate;
                        break 2;
                    }
                }
            }
            if ($matchedCcaa === null) {
                continue;
            }

            $indicator = null;
            foreach ($metaNames as $name) {
                if ($this->matchesLoose($name, 'Grado de ocupación por plazas')) {
                    $indicator = 'occupancy';
                    break;
                }
                if ($this->matchesLoose($name, 'Estancia media')) {
                    $indicator = 'stay';
                    break;
                }
            }
            if ($indicator === null) {
                continue;
            }

            $data = $series['Data'] ?? [];
            $latest = $data[0]['Valor'] ?? null;
            $prior = $data[1]['Valor'] ?? null;

            if ($indicator === 'occupancy' && $latest !== null) {
                $occupancyByCcaa[$matchedCcaa] = [
                    'rate' => (float) $latest,
                    'delta' => $prior !== null ? round((float) $latest - (float) $prior, 1) : null,
                ];
            }

            if ($indicator === 'stay' && $latest !== null) {
                $stayByCcaa[$matchedCcaa] = round((float) $latest, 1);
            }
        }

        $out = [];
        foreach ($occupancyByCcaa as $ccaa => $occ) {
            $out[$ccaa] = [
                'rate' => $occ['rate'],
                'delta' => $occ['delta'],
                'stay' => $stayByCcaa[$ccaa] ?? null,
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array{0: string, 1: float}>
     */
    private function getFronturTopOrigins(int $limit = 5): array
    {
        $raw = Cache::remember('market_intel:ine:frontur:v1', now()->addDays(7), function () {
            $response = Http::timeout(5)->get(self::FRONTUR_TABLE_URL);

            if (! $response->successful()) {
                return [];
            }

            return $response->json() ?? [];
        });

        if (! is_array($raw) || empty($raw)) {
            return [];
        }

        $totals = [];
        foreach ($raw as $series) {
            $metaNames = array_map(
                fn ($m) => is_array($m) ? ($m['Nombre'] ?? '') : '',
                $series['MetaData'] ?? []
            );

            $isCountryBreakdown = false;
            $country = null;
            foreach ($metaNames as $name) {
                if ($name === 'Total' || $name === '') {
                    continue;
                }
                if ($this->matchesLoose($name, 'Total turistas') || $this->matchesLoose($name, 'dato')) {
                    $isCountryBreakdown = true;

                    continue;
                }
                if (mb_strlen($name) <= 30 && ! preg_match('/total|dato|valor|número/i', $name)) {
                    $country = $name;
                }
            }

            if (! $isCountryBreakdown || $country === null || $this->matchesLoose($country, 'Total')) {
                continue;
            }

            $value = $series['Data'][0]['Valor'] ?? null;
            if ($value === null) {
                continue;
            }

            $totals[$country] = ($totals[$country] ?? 0) + (float) $value;
        }

        if (empty($totals)) {
            return [];
        }

        $grandTotal = array_sum($totals);
        if ($grandTotal <= 0) {
            return [];
        }

        arsort($totals);
        $top = array_slice($totals, 0, $limit, true);
        $out = [];
        foreach ($top as $country => $value) {
            $out[] = [$country, round(($value / $grandTotal) * 100, 1)];
        }

        return $out;
    }

    private function matchesLoose(string $haystack, string $needle): bool
    {
        $normalize = fn (string $s) => Str::lower(Str::ascii(trim($s)));

        return str_contains($normalize($haystack), $normalize($needle));
    }

    /**
     * @param  array<int, array{city: string, country: string}>  $cities
     * @param  array<string, string>  $weather
     * @param  array<string, array{rate: float, delta: ?float, stay: ?float}>  $eoh
     * @param  array<int, array{0: string, 1: float}>  $frontur
     */
    private function formatBlob(array $cities, array $weather, array $eoh, array $frontur): string
    {
        $lines = [];
        $lines[] = '=== INTELIGENCIA DE MERCADO (fuentes: INE EOH/FRONTUR + Open-Meteo) ===';
        $lines[] = '';

        if (! empty($weather)) {
            $lines[] = 'Previsión meteorológica (próximos 7 días):';
            foreach ($weather as $cityName => $summary) {
                $display = Str::title(mb_strtolower($cityName));
                $lines[] = "- {$display}: {$summary}";
            }
            $lines[] = '';
        } else {
            $lines[] = 'Previsión meteorológica: datos no disponibles.';
            $lines[] = '';
        }

        if (! empty($eoh)) {
            $lines[] = 'Ocupación hotelera España (INE EOH, último mes disponible):';
            foreach ($eoh as $ccaa => $row) {
                $rate = number_format($row['rate'], 1, ',', '');
                $deltaStr = '';
                if ($row['delta'] !== null) {
                    $sign = $row['delta'] >= 0 ? '+' : '';
                    $deltaStr = " ({$sign}".number_format($row['delta'], 1, ',', '').'pp vs mes anterior)';
                }
                $stayStr = $row['stay'] !== null
                    ? ", estancia media ".number_format($row['stay'], 1, ',', '').' noches'
                    : '';
                $lines[] = "- {$ccaa}: {$rate}% plazas{$deltaStr}{$stayStr}.";
            }
            $lines[] = '';
        }

        if (! empty($frontur)) {
            $pairs = array_map(
                fn ($row) => "{$row[0]} ".number_format($row[1], 1, ',', '').'%',
                $frontur
            );
            $lines[] = 'Mercados emisores internacionales (INE FRONTUR, último mes):';
            $lines[] = implode(', ', $pairs).'.';
            $lines[] = '';
        }

        $hasNonEs = collect($cities)->contains(fn ($c) => strtoupper($c['country'] ?? '') !== 'ES');
        if ($hasNonEs) {
            $lines[] = 'Nota: los datos del INE cubren solo hoteles en España. Para hoteles fuera de España usa únicamente la previsión meteorológica.';
        }

        return rtrim(implode("\n", $lines))."\n";
    }
}
