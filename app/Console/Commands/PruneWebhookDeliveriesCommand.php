<?php

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class PruneWebhookDeliveriesCommand extends Command
{
    protected $signature = 'webhooks:prune-deliveries {--days=7 : Retention window in days}';

    protected $description = 'Delete webhook delivery rows older than the retention window';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = WebhookDelivery::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} webhook delivery rows older than {$days} days.");

        return self::SUCCESS;
    }
}
