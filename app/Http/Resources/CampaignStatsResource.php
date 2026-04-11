<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use App\Services\Email\CampaignStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wrapper around CampaignStatsService so the API returns a consistent shape
 * across the six analytics queries. The constructor takes a Campaign, not a
 * model attribute, because the resource itself runs the queries through the
 * service.
 */
class CampaignStatsResource extends JsonResource
{
    public function __construct(Campaign $campaign)
    {
        parent::__construct($campaign);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Campaign $campaign */
        $campaign = $this->resource;
        $service = new CampaignStatsService();

        return [
            'campaign_id' => $campaign->id,
            'summary' => $service->forCampaign($campaign),
            'funnel' => $service->funnelFor($campaign),
            'time_series' => $service->timeSeriesFor($campaign),
            'country_breakdown' => $service->countryBreakdownFor($campaign),
            'segment_breakdown' => $service->segmentBreakdownFor($campaign),
            'followup_performance' => $service->followupPerformanceFor($campaign),
        ];
    }
}
