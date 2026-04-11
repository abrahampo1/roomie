<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Campaign\CampaignPipeline;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunCampaignPipeline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public Campaign $campaign,
    ) {}

    public function handle(): void
    {
        try {
            $client = LlmClientFactory::make(
                $this->campaign->api_provider,
                $this->campaign->api_key,
                $this->campaign->api_base_url,
                $this->campaign->api_model,
            );

            (new CampaignPipeline($client, new MarketIntelligenceService))->run($this->campaign);
        } catch (Throwable $e) {
            if ($this->campaign->status !== 'failed') {
                $this->campaign->update(['status' => 'failed']);
            }

            throw $e;
        }

        // The key is intentionally NOT wiped here. It stays encrypted on the
        // campaign row so the user doesn't have to re-paste it when they send
        // the campaign or enable follow-ups. Retention is bounded by
        // `api_key_retention_expires_at` (set at campaign creation) and by the
        // `WipeExpiredCampaignKeysCommand` that runs hourly.
    }

    /**
     * Fire the terminal `campaign.failed` webhook when the queue worker gives
     * up. The pipeline's own catch block already emits this on in-process
     * failures; this handler covers the edge case where the job itself dies
     * before or after the pipeline had a chance to catch.
     */
    public function failed(Throwable $exception): void
    {
        $campaign = $this->campaign->fresh() ?? $this->campaign;

        WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.failed', [
            'campaign_id' => $campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
