<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Aggregate KPIs across all of a user's campaigns.
     *
     * @return array{
     *     total_campaigns: int, completed_campaigns: int, failed_campaigns: int,
     *     total_recipients: int, total_sent: int,
     *     total_opened: int, total_clicked: int, total_converted: int, total_unsubscribed: int,
     *     open_rate: float, click_rate: float, conversion_rate: float,
     *     avg_quality_score: float|null
     * }
     */
    public function aggregateKpis(User $user): array
    {
        $campaignStats = DB::table('campaigns')
            ->where('user_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                AVG(CASE WHEN quality_score IS NOT NULL THEN quality_score END) as avg_quality
            ")
            ->first();

        $recipientStats = DB::table('campaign_recipients as r')
            ->join('campaigns as c', 'r.campaign_id', '=', 'c.id')
            ->where('c.user_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN r.status IN ('sent', 'converted', 'unsubscribed') THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN r.first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN r.first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN r.status = 'converted' THEN 1 ELSE 0 END) as converted,
                SUM(CASE WHEN r.status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed
            ")
            ->first();

        $sent = (int) ($recipientStats->sent ?? 0);
        $denominator = max(1, $sent);

        return [
            'total_campaigns' => (int) ($campaignStats->total ?? 0),
            'completed_campaigns' => (int) ($campaignStats->completed ?? 0),
            'failed_campaigns' => (int) ($campaignStats->failed ?? 0),
            'total_recipients' => (int) ($recipientStats->total ?? 0),
            'total_sent' => $sent,
            'total_opened' => (int) ($recipientStats->opened ?? 0),
            'total_clicked' => (int) ($recipientStats->clicked ?? 0),
            'total_converted' => (int) ($recipientStats->converted ?? 0),
            'total_unsubscribed' => (int) ($recipientStats->unsubscribed ?? 0),
            'open_rate' => round(((int) ($recipientStats->opened ?? 0)) / $denominator * 100, 1),
            'click_rate' => round(((int) ($recipientStats->clicked ?? 0)) / $denominator * 100, 1),
            'conversion_rate' => round(((int) ($recipientStats->converted ?? 0)) / $denominator * 100, 1),
            'avg_quality_score' => $campaignStats->avg_quality ? round((float) $campaignStats->avg_quality, 0) : null,
        ];
    }

    /**
     * Cross-campaign conversion funnel.
     *
     * @return array<int, array{label: string, count: int, pct_total: float, pct_prev: float}>
     */
    public function crossCampaignFunnel(User $user): array
    {
        $kpis = $this->aggregateKpis($user);

        $stages = [
            ['label' => 'Disparados', 'count' => $kpis['total_recipients']],
            ['label' => 'Entregados', 'count' => $kpis['total_sent']],
            ['label' => 'Abiertos', 'count' => $kpis['total_opened']],
            ['label' => 'Clickados', 'count' => $kpis['total_clicked']],
            ['label' => 'Convertidos', 'count' => $kpis['total_converted']],
        ];

        $base = max(1, $kpis['total_recipients']);
        $out = [];
        $previous = $kpis['total_recipients'];
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
     * Daily opens/clicks over the last N days across all user campaigns.
     *
     * @return array{buckets: array<int, array{label: string, date: string, opens: int, clicks: int}>, has_enough_data: bool}
     */
    public function recentActivity(User $user, int $days = 30): array
    {
        $since = now()->subDays($days)->startOfDay();

        $rows = DB::table('campaign_recipients as r')
            ->join('campaigns as c', 'r.campaign_id', '=', 'c.id')
            ->where('c.user_id', $user->id)
            ->where(function ($q) use ($since) {
                $q->where('r.first_opened_at', '>=', $since)
                    ->orWhere('r.first_clicked_at', '>=', $since);
            })
            ->get(['r.first_opened_at', 'r.first_clicked_at']);

        $buckets = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - 1 - $i)->format('Y-m-d');
            $buckets[$date] = ['opens' => 0, 'clicks' => 0];
        }

        foreach ($rows as $row) {
            if ($row->first_opened_at) {
                $date = substr((string) $row->first_opened_at, 0, 10);
                if (isset($buckets[$date])) {
                    $buckets[$date]['opens']++;
                }
            }
            if ($row->first_clicked_at) {
                $date = substr((string) $row->first_clicked_at, 0, 10);
                if (isset($buckets[$date])) {
                    $buckets[$date]['clicks']++;
                }
            }
        }

        $result = [];
        $totalActivity = 0;
        foreach ($buckets as $date => $counts) {
            $totalActivity += $counts['opens'] + $counts['clicks'];
            $result[] = [
                'label' => date('d M', strtotime($date)),
                'date' => $date,
                'opens' => $counts['opens'],
                'clicks' => $counts['clicks'],
            ];
        }

        return [
            'buckets' => $result,
            'has_enough_data' => $totalActivity > 0,
        ];
    }

    /**
     * Top N campaigns by conversion rate (among those with at least 1 sent).
     *
     * @return array<int, array{id: int, name: string, objective: string, status: string, quality_score: int|null, sent: int, converted: int, conversion_rate: float}>
     */
    public function topCampaigns(User $user, int $limit = 5): array
    {
        $rows = DB::table('campaigns as c')
            ->leftJoin('campaign_recipients as r', 'c.id', '=', 'r.campaign_id')
            ->where('c.user_id', $user->id)
            ->groupBy('c.id', 'c.name', 'c.objective', 'c.status', 'c.quality_score', 'c.created_at')
            ->selectRaw("
                c.id,
                c.name,
                c.objective,
                c.status,
                c.quality_score,
                c.created_at,
                SUM(CASE WHEN r.status IN ('sent', 'converted', 'unsubscribed') THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN r.status = 'converted' THEN 1 ELSE 0 END) as converted
            ")
            ->havingRaw("SUM(CASE WHEN r.status IN ('sent', 'converted', 'unsubscribed') THEN 1 ELSE 0 END) > 0")
            ->orderByDesc(DB::raw("SUM(CASE WHEN r.status = 'converted' THEN 1 ELSE 0 END) * 1.0 / NULLIF(SUM(CASE WHEN r.status IN ('sent', 'converted', 'unsubscribed') THEN 1 ELSE 0 END), 0)"))
            ->limit($limit)
            ->get();

        return $rows->map(function ($row) {
            $sent = max(1, (int) $row->sent);

            return [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'objective' => (string) $row->objective,
                'status' => (string) $row->status,
                'quality_score' => $row->quality_score ? (int) $row->quality_score : null,
                'sent' => (int) $row->sent,
                'converted' => (int) $row->converted,
                'conversion_rate' => round(((int) $row->converted) / $sent * 100, 1),
                'created_at' => (string) $row->created_at,
            ];
        })->all();
    }

    /**
     * Cross-campaign country breakdown.
     *
     * @return array<int, array{country: string, sent: int, opened: int, clicked: int, pct: float}>
     */
    public function countryBreakdown(User $user, int $limit = 5): array
    {
        $rows = DB::table('campaign_recipients as r')
            ->join('campaigns as c', 'r.campaign_id', '=', 'c.id')
            ->leftJoin('customers as cu', 'r.customer_id', '=', 'cu.id')
            ->where('c.user_id', $user->id)
            ->whereNotNull('cu.country_guest')
            ->groupBy('cu.country_guest')
            ->selectRaw('
                cu.country_guest as country,
                COUNT(*) as sent,
                SUM(CASE WHEN r.first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN r.first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ')
            ->orderByDesc('opened')
            ->limit($limit)
            ->get();

        $max = max(1, $rows->max('opened') ?? 1);

        return $rows->map(fn ($row) => [
            'country' => (string) $row->country,
            'sent' => (int) $row->sent,
            'opened' => (int) $row->opened,
            'clicked' => (int) $row->clicked,
            'pct' => round(((int) $row->opened) / $max * 100, 1),
        ])->all();
    }

    /**
     * Cross-campaign demographic breakdown.
     *
     * @return array{age_range: array, gender: array}
     */
    public function segmentBreakdown(User $user): array
    {
        return [
            'age_range' => $this->queryDemographic($user, 'cu.age_range'),
            'gender' => $this->queryDemographic($user, 'cu.gender'),
        ];
    }

    /**
     * @return array<int, array{label: string, sent: int, opened: int, clicked: int, pct: float}>
     */
    private function queryDemographic(User $user, string $groupColumn): array
    {
        $rows = DB::table('campaign_recipients as r')
            ->join('campaigns as c', 'r.campaign_id', '=', 'c.id')
            ->leftJoin('customers as cu', 'r.customer_id', '=', 'cu.id')
            ->where('c.user_id', $user->id)
            ->whereNotNull($groupColumn)
            ->groupBy($groupColumn)
            ->selectRaw("
                {$groupColumn} as label,
                COUNT(*) as sent,
                SUM(CASE WHEN r.first_opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN r.first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ")
            ->orderByDesc('opened')
            ->get();

        $max = max(1, $rows->max('opened') ?? 1);

        return $rows->map(fn ($row) => [
            'label' => (string) $row->label,
            'sent' => (int) $row->sent,
            'opened' => (int) $row->opened,
            'clicked' => (int) $row->clicked,
            'pct' => round(((int) $row->opened) / $max * 100, 1),
        ])->all();
    }
}
