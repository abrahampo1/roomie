<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Campaign\CampaignPipeline;
use App\Services\LLM\LlmClientFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

            (new CampaignPipeline($client))->run($this->campaign);
        } catch (\Throwable $e) {
            if ($this->campaign->status !== 'failed') {
                $this->campaign->update(['status' => 'failed']);
            }

            throw $e;
        } finally {
            // BYOK: never persist the user's API key beyond the run.
            $this->campaign->update(['api_key' => null]);
        }
    }
}
