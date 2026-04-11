<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Email\CampaignSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Per-chunk worker. Loops over up to 20 recipient ids and delivers the
 * email through CampaignSender::sendOne() for each. Failures on individual
 * recipients are isolated so one bad row never kills the chunk.
 *
 * Accepts an optional `$creativeOverride` that, when present, replaces the
 * campaign's primary creative — used for follow-up variants in Step 7.
 */
class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public int $timeout = 120;

    /**
     * @param  array<int, int>  $recipientIds
     * @param  array<string, mixed>|null  $creativeOverride
     */
    public function __construct(
        public int $campaignId,
        public array $recipientIds,
        public ?array $creativeOverride = null,
    ) {}

    public function handle(CampaignSender $sender): void
    {
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null) {
            return;
        }

        $creative = $this->creativeOverride ?? $campaign->creative ?? [];
        $strategy = $campaign->strategy ?? [];

        if (empty($creative)) {
            Log::warning('SendCampaignEmailJob: no creative to send', [
                'campaign_id' => $this->campaignId,
            ]);

            return;
        }

        $recipients = CampaignRecipient::query()
            ->whereIn('id', $this->recipientIds)
            ->get();

        foreach ($recipients as $recipient) {
            try {
                $sender->sendOne($recipient, $creative, $strategy);
            } catch (Throwable $e) {
                Log::error('SendCampaignEmailJob: unhandled failure', [
                    'recipient_id' => $recipient->id,
                    'campaign_id' => $this->campaignId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
