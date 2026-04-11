<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Demo-only engagement simulator. Dispatched from CampaignSender::sendOne()
 * with a random delay when `roomie.allow_real_sends` is false. When it runs,
 * it rolls the dice against realistic email-marketing rates and mutates the
 * recipient row accordingly so the dashboard ticks up live during a demo.
 *
 * Idempotent: if the recipient is no longer in status `sent` (because someone
 * clicked the real URL from the log, or the user flipped the status manually),
 * the job returns without touching anything.
 */
class SimulateRecipientEngagementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(
        public int $recipientId,
    ) {}

    public function handle(): void
    {
        $recipient = CampaignRecipient::query()->find($this->recipientId);

        if ($recipient === null || $recipient->status !== 'sent') {
            return;
        }

        $roll = random_int(1, 1000); // 1..1000 for 0.1% granularity

        // 3% bounce
        if ($roll <= 30) {
            $recipient->update([
                'status' => 'bounced',
                'bounced_at' => now(),
                'bounce_reason' => 'Simulated: mailbox unknown',
            ]);

            return;
        }

        // 1% unsubscribe
        if ($roll <= 40) {
            $recipient->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return;
        }

        // 35% opened (roll 41..390)
        if ($roll <= 390) {
            $opensCount = random_int(1, 3);
            $update = [
                'opens_count' => $opensCount,
                'first_opened_at' => now(),
                'last_opened_at' => now()->addSeconds(random_int(0, 120)),
            ];

            // Of those opened, 25% click through (rolled independently)
            if (random_int(1, 100) <= 25) {
                $clickDelay = random_int(5, 30);
                $update['clicks_count'] = 1;
                $update['first_clicked_at'] = now()->addSeconds($clickDelay);
                $update['last_clicked_at'] = now()->addSeconds($clickDelay);
                $update['converted_at'] = now()->addSeconds($clickDelay + random_int(10, 60));
                $update['status'] = 'converted';
            }

            $recipient->update($update);

            return;
        }

        // Rest (~61%): silent sent, eligible for follow-ups.
    }
}
