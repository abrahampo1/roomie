<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class WipeExpiredCampaignKeysCommand extends Command
{
    protected $signature = 'campaigns:wipe-expired-keys';

    protected $description = 'Safety net: wipe any retained LLM API key past its expiration timestamp';

    public function handle(): int
    {
        $expired = Campaign::query()
            ->where('api_key_retained_for_followups', true)
            ->whereNotNull('api_key_retention_expires_at')
            ->where('api_key_retention_expires_at', '<', now())
            ->get();

        foreach ($expired as $campaign) {
            $campaign->update([
                'api_key' => null,
                'api_key_retained_for_followups' => false,
                'api_key_retention_expires_at' => null,
                'followups_enabled' => false,
            ]);

            $this->info("[campaign {$campaign->id}] retention expired, key wiped");
        }

        return self::SUCCESS;
    }
}
