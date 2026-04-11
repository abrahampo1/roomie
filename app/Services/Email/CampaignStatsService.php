<?php

namespace App\Services\Email;

use App\Models\Campaign;
use Illuminate\Support\Facades\DB;

class CampaignStatsService
{
    /**
     * Aggregate counters for the footer summary block. Runs as a single query.
     *
     * @return array{
     *     total: int, sent: int, bounced: int, failed: int,
     *     opened: int, clicked: int, unsubscribed: int, converted: int,
     *     total_opens: int, total_clicks: int,
     *     open_rate: float, click_rate: float, unsubscribe_rate: float, conversion_rate: float
     * }
     */
    public function forCampaign(Campaign $campaign): array
    {
        $row = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('sent', 'converted', 'unsubscribed') THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                COALESCE(SUM(opens_count), 0) as total_opens,
                COALESCE(SUM(clicks_count), 0) as total_clicks
            ")
            ->first();

        $total = (int) ($row->total ?? 0);
        $sent = (int) ($row->sent ?? 0);
        $denominator = max(1, $sent);

        return [
            'total' => $total,
            'sent' => $sent,
            'bounced' => (int) ($row->bounced ?? 0),
            'failed' => (int) ($row->failed ?? 0),
            'opened' => (int) ($row->opened ?? 0),
            'clicked' => (int) ($row->clicked ?? 0),
            'unsubscribed' => (int) ($row->unsubscribed ?? 0),
            'converted' => (int) ($row->converted ?? 0),
            'total_opens' => (int) ($row->total_opens ?? 0),
            'total_clicks' => (int) ($row->total_clicks ?? 0),
            'open_rate' => round(((int) ($row->opened ?? 0)) / $denominator * 100, 1),
            'click_rate' => round(((int) ($row->clicked ?? 0)) / $denominator * 100, 1),
            'unsubscribe_rate' => round(((int) ($row->unsubscribed ?? 0)) / $denominator * 100, 1),
            'conversion_rate' => round(((int) ($row->converted ?? 0)) / $denominator * 100, 1),
        ];
    }

    /**
     * Five-stage conversion funnel: Enviados → Entregados → Abiertos → Clickados → Convertidos.
     * Each stage returns the absolute count, the percentage vs the total sent,
     * and the percentage retention vs the previous stage.
     *
     * @return array<int, array{label: string, count: int, pct_total: float, pct_prev: float}>
     */
    public function funnelFor(Campaign $campaign): array
    {
        $stats = $this->forCampaign($campaign);

        $total = $stats['total'];
        $sent = $stats['sent'];
        $delivered = $sent; // No async bounce webhook — delivered == sent for now
        $opened = $stats['opened'];
        $clicked = $stats['clicked'];
        $converted = $stats['converted'];

        $stages = [
            ['label' => 'Disparados', 'count' => $total],
            ['label' => 'Entregados', 'count' => $delivered],
            ['label' => 'Abiertos', 'count' => $opened],
            ['label' => 'Clickados', 'count' => $clicked],
            ['label' => 'Convertidos', 'count' => $converted],
        ];

        $base = max(1, $total);
        $out = [];
        $previous = $total;
        foreach ($stages as $stage) {
            $count = (int) $stage['count'];
            $out[] = [
                'label' => $stage['label'],
                'count' => $count,
                'pct_total' => round(($count / $base) * 100, 1),
                'pct_prev' => $previous > 0 ? round(($count / $previous) * 100, 1) : 0.0,
            ];
            $previous = max(1, $count);
        }

        return $out;
    }

    /**
     * Opens + clicks bucketed by a fixed time window since the campaign was
     * sent. Used to draw a simple SVG line chart. Buckets are grouped on the
     * PHP side to stay portable across SQLite/MySQL.
     *
     * @return array{
     *     buckets: array<int, array{label: string, at: string, opens: int, clicks: int}>,
     *     has_enough_data: bool
     * }
     */
    public function timeSeriesFor(Campaign $campaign, int $bucketMinutes = 5): array
    {
        $since = $campaign->sent_at ?: now()->subHour();

        $rows = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where(function ($q) {
                $q->whereNotNull('first_opened_at')
                    ->orWhereNotNull('first_clicked_at');
            })
            ->get(['first_opened_at', 'first_clicked_at']);

        $bucketSize = $bucketMinutes * 60;
        $startTs = $since->getTimestamp();
        $endTs = now()->getTimestamp();
        $totalBuckets = max(1, (int) ceil(($endTs - $startTs) / $bucketSize));
        $totalBuckets = min($totalBuckets, 60); // cap at 60 buckets

        $opens = array_fill(0, $totalBuckets, 0);
        $clicks = array_fill(0, $totalBuckets, 0);

        foreach ($rows as $row) {
            if ($row->first_opened_at !== null) {
                $ts = strtotime((string) $row->first_opened_at);
                $idx = max(0, min($totalBuckets - 1, (int) floor(($ts - $startTs) / $bucketSize)));
                $opens[$idx]++;
            }
            if ($row->first_clicked_at !== null) {
                $ts = strtotime((string) $row->first_clicked_at);
                $idx = max(0, min($totalBuckets - 1, (int) floor(($ts - $startTs) / $bucketSize)));
                $clicks[$idx]++;
            }
        }

        $buckets = [];
        for ($i = 0; $i < $totalBuckets; $i++) {
            $bucketTs = $startTs + $i * $bucketSize;
            $buckets[] = [
                'label' => date('H:i', $bucketTs),
                'at' => date('c', $bucketTs),
                'opens' => $opens[$i],
                'clicks' => $clicks[$i],
            ];
        }

        $nonEmpty = array_sum($opens) + array_sum($clicks);

        return [
            'buckets' => $buckets,
            'has_enough_data' => $totalBuckets >= 3 && $nonEmpty > 0,
        ];
    }

    /**
     * Top N countries by opened/clicked signal. Joins campaign_recipients on
     * customers via customer_id (FK, indexed).
     *
     * @return array<int, array{country: string, sent: int, opened: int, clicked: int, pct: float}>
     */
    public function countryBreakdownFor(Campaign $campaign, int $limit = 5): array
    {
        $rows = DB::table('campaign_recipients as r')
            ->leftJoin('customers as c', 'r.customer_id', '=', 'c.id')
            ->where('r.campaign_id', $campaign->id)
            ->whereNotNull('c.country_guest')
            ->groupBy('c.country_guest')
            ->selectRaw("
                c.country_guest as country,
                COUNT(*) as sent,
                SUM(CASE WHEN r.first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN r.first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ")
            ->orderByDesc('opened')
            ->orderByDesc('sent')
            ->limit($limit)
            ->get();

        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) $row->opened);
        }
        $max = max(1, $max);

        $out = [];
        foreach ($rows as $row) {
            $opened = (int) $row->opened;
            $out[] = [
                'country' => (string) $row->country,
                'sent' => (int) $row->sent,
                'opened' => $opened,
                'clicked' => (int) $row->clicked,
                'pct' => round(($opened / $max) * 100, 1),
            ];
        }

        return $out;
    }

    /**
     * Engagement breakdown by two customer demographics: age range and gender.
     * Each returns rows sorted by opened desc.
     *
     * @return array{
     *     age_range: array<int, array{label: string, sent: int, opened: int, clicked: int, pct: float}>,
     *     gender: array<int, array{label: string, sent: int, opened: int, clicked: int, pct: float}>
     * }
     */
    public function segmentBreakdownFor(Campaign $campaign): array
    {
        return [
            'age_range' => $this->queryDemographic($campaign, 'c.age_range'),
            'gender' => $this->queryDemographic($campaign, 'c.gender'),
        ];
    }

    /**
     * Follow-up attempt performance. One row per distinct `attempts_sent` value
     * with opens / clicks / send counts. Lets the dashboard visualise whether
     * the escalation strategy actually increases engagement per attempt.
     *
     * @return array<int, array{attempt: int, sent: int, opened: int, clicked: int, open_rate: float, click_rate: float}>
     */
    public function followupPerformanceFor(Campaign $campaign): array
    {
        $rows = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('attempts_sent', '>', 0)
            ->groupBy('attempts_sent')
            ->orderBy('attempts_sent')
            ->selectRaw("
                attempts_sent as attempt,
                COUNT(*) as sent,
                SUM(CASE WHEN first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ")
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $sent = max(1, (int) $row->sent);
            $out[] = [
                'attempt' => (int) $row->attempt,
                'sent' => (int) $row->sent,
                'opened' => (int) $row->opened,
                'clicked' => (int) $row->clicked,
                'open_rate' => round(((int) $row->opened) / $sent * 100, 1),
                'click_rate' => round(((int) $row->clicked) / $sent * 100, 1),
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array{label: string, sent: int, opened: int, clicked: int, pct: float}>
     */
    private function queryDemographic(Campaign $campaign, string $groupColumn): array
    {
        $rows = DB::table('campaign_recipients as r')
            ->leftJoin('customers as c', 'r.customer_id', '=', 'c.id')
            ->where('r.campaign_id', $campaign->id)
            ->whereNotNull($groupColumn)
            ->groupBy($groupColumn)
            ->selectRaw("
                {$groupColumn} as label,
                COUNT(*) as sent,
                SUM(CASE WHEN r.first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN r.first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ")
            ->orderByDesc('opened')
            ->orderByDesc('sent')
            ->get();

        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) $row->opened);
        }
        $max = max(1, $max);

        $out = [];
        foreach ($rows as $row) {
            $opened = (int) $row->opened;
            $out[] = [
                'label' => (string) $row->label,
                'sent' => (int) $row->sent,
                'opened' => $opened,
                'clicked' => (int) $row->clicked,
                'pct' => round(($opened / $max) * 100, 1),
            ];
        }

        return $out;
    }
}
