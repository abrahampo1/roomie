<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignPipeline;
use App\Services\LLM\LlmClientFactory;
use App\Services\MarketIntelligence\MarketIntelligenceService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Console\Command;
use Throwable;

class ProcessCampaignFollowupsCommand extends Command
{
    protected $signature = 'campaigns:process-followups';

    protected $description = 'Generate and dispatch follow-up emails for campaigns with active retention';

    public function handle(): int
    {
        $campaigns = Campaign::query()
            ->where('followups_enabled', true)
            ->where('api_key_retained_for_followups', true)
            ->whereNotNull('api_key')
            ->where('api_key_retention_expires_at', '>', now())
            ->get();

        foreach ($campaigns as $campaign) {
            $this->processCampaign($campaign);
        }

        return self::SUCCESS;
    }

    private function processCampaign(Campaign $campaign): void
    {
        $eligible = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', 'sent')
            ->where('attempts_sent', '<', $campaign->followup_max_attempts)
            ->where(function ($q) {
                $q->whereNull('next_followup_not_before')
                    ->orWhere('next_followup_not_before', '<', now());
            })
            ->whereNull('unsubscribed_at')
            ->get();

        if ($eligible->isEmpty()) {
            $stillHasWork = CampaignRecipient::query()
                ->where('campaign_id', $campaign->id)
                ->where('status', 'sent')
                ->where('attempts_sent', '<', $campaign->followup_max_attempts)
                ->whereNull('unsubscribed_at')
                ->exists();

            if (! $stillHasWork) {
                $this->wipeKey($campaign, 'no more eligible recipients');
            }

            return;
        }

        $byAttempt = $eligible->groupBy(fn (CampaignRecipient $r) => $r->attempts_sent + 1);

        foreach ($byAttempt as $attempt => $recipients) {
            $attempt = (int) $attempt;

            $creative = $this->ensureVariant($campaign, $attempt);
            if ($creative === null) {
                $this->warn("[campaign {$campaign->id}] could not generate creative for attempt {$attempt}");

                continue;
            }

            $ids = $recipients->pluck('id')->all();
            foreach (array_chunk($ids, 20) as $index => $chunk) {
                SendCampaignEmailJob::dispatch($campaign->id, $chunk, $creative)
                    ->delay(now()->addSeconds($index * 5));
            }

            WebhookDispatcher::dispatchCampaignEvent($campaign, 'campaign.followup_started', [
                'campaign_id' => $campaign->id,
                'attempt' => $attempt,
                'recipient_count' => count($ids),
            ]);

            $cooldownHours = max(1, (int) $campaign->followup_cooldown_hours);
            $nextEligibleAt = now()->addHours($cooldownHours * $attempt);
            foreach ($recipients as $recipient) {
                $recipient->update(['next_followup_not_before' => $nextEligibleAt]);
            }

            $this->info("[campaign {$campaign->id}] dispatched attempt {$attempt} to ".count($ids).' recipients');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function ensureVariant(Campaign $campaign, int $attempt): ?array
    {
        $variants = $campaign->followup_variants ?? [];
        if (isset($variants[$attempt])) {
            return $variants[$attempt];
        }

        try {
            $client = LlmClientFactory::make(
                $campaign->api_provider,
                $campaign->api_key,
                $campaign->api_base_url,
                $campaign->api_model,
            );
            $pipeline = new CampaignPipeline($client, new MarketIntelligenceService);
            $creative = $pipeline->regenerateForFollowup($campaign, $attempt);

            $variants[$attempt] = $creative;
            $campaign->update(['followup_variants' => $variants]);

            return $creative;
        } catch (Throwable $e) {
            $this->error("[campaign {$campaign->id}] variant generation failed: ".$e->getMessage());

            return null;
        }
    }

    private function wipeKey(Campaign $campaign, string $reason): void
    {
        $campaign->update([
            'api_key' => null,
            'api_key_retained_for_followups' => false,
            'api_key_retention_expires_at' => null,
            'followups_enabled' => false,
        ]);

        $this->info("[campaign {$campaign->id}] wiped retained key ({$reason})");
    }
}
