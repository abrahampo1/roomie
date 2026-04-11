<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Parent dispatcher. Reads the recipients for a campaign, chunks them, and
 * queues one SendCampaignEmailJob per chunk with staggered delays so the
 * mail provider's rate limit stays happy. Finishes immediately — the actual
 * sending happens in the children.
 */
class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(
        public int $campaignId,
    ) {}

    public function handle(): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null) {
            return;
        }

        $recipientIds = $campaign->recipients()
            ->where('status', 'queued')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (empty($recipientIds)) {
            return;
        }

        $chunks = array_chunk($recipientIds, 20);
        foreach ($chunks as $index => $chunk) {
            SendCampaignEmailJob::dispatch($campaign->id, $chunk)
                ->delay(now()->addSeconds($index * 5));
        }
    }
}
