<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Campaign\CampaignPipeline;
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
        $pipeline = new CampaignPipeline(
            apiKey: config('services.anthropic.api_key'),
        );

        $pipeline->run($this->campaign);
    }
}
