<?php

namespace App\Services\Email;

use App\Models\Campaign;
use Illuminate\Support\Facades\DB;

class CampaignStatsService
{
    /**
     * Aggregate counters for the stats panel. Runs as a single query.
     *
     * @return array{
     *     total: int,
     *     sent: int,
     *     bounced: int,
     *     failed: int,
     *     opened: int,
     *     clicked: int,
     *     unsubscribed: int,
     *     converted: int,
     *     total_opens: int,
     *     total_clicks: int,
     *     open_rate: float,
     *     click_rate: float,
     *     unsubscribe_rate: float,
     *     conversion_rate: float
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
}
