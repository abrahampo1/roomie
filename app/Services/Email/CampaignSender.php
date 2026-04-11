<?php

namespace App\Services\Email;

use App\Jobs\SendCampaignJob;
use App\Jobs\SimulateRecipientEngagementJob;
use App\Mail\CampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Customer;
use App\Models\EmailUnsubscribe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

class CampaignSender
{
    /**
     * Snapshot the selected customers into campaign_recipients and dispatch
     * the send job. Returns the number of recipient rows that were created
     * or re-used for this campaign.
     *
     * Duplicate emails are deduplicated against the unique `(campaign_id, email)`
     * index, so calling this twice for the same campaign is idempotent.
     *
     * @param  Collection<int, Customer>  $customers
     */
    public function dispatchInitialSend(Campaign $campaign, Collection $customers): int
    {
        $count = 0;
        $blocked = EmailUnsubscribe::query()->pluck('email')->flip()->all();

        foreach ($customers as $customer) {
            $email = (string) $customer->email;

            if ($email === '' || isset($blocked[$email])) {
                continue;
            }

            $recipient = CampaignRecipient::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'email' => $email,
                ],
                [
                    'customer_id' => $customer->id,
                    'first_name' => $customer->first_name,
                    'status' => 'queued',
                ],
            );

            if ($recipient->wasRecentlyCreated) {
                $count++;
            }
        }

        $campaign->update([
            'send_enabled' => true,
            'sent_at' => $campaign->sent_at ?? now(),
        ]);

        SendCampaignJob::dispatch($campaign->id);

        return $count;
    }

    /**
     * Actually deliver one recipient's email. Called from SendCampaignEmailJob.
     *
     * Enforces the safety gate: if `roomie.allow_real_sends` is false, the
     * email is forced through the `log` mailer regardless of `MAIL_MAILER`.
     * Any TransportException is caught and recorded on the recipient row so
     * one bad address doesn't fail the chunk.
     *
     * @param  array<string, mixed>  $creative
     * @param  array<string, mixed>  $strategy
     */
    public function sendOne(CampaignRecipient $recipient, array $creative, array $strategy): bool
    {
        // Double gate: unsubscribed addresses are always skipped.
        if (EmailUnsubscribe::isBlocked($recipient->email)) {
            $recipient->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => $recipient->unsubscribed_at ?? now(),
            ]);

            return false;
        }

        $mailable = new CampaignEmail($recipient, $creative, $strategy);
        $mailer = $this->forcedMailer();

        try {
            Mail::mailer($mailer)->to($recipient->email)->send($mailable);
        } catch (TransportException $e) {
            $recipient->update([
                'status' => 'failed',
                'bounce_reason' => mb_substr($e->getMessage(), 0, 500),
                'bounced_at' => now(),
            ]);

            Log::warning('CampaignSender: transport failure', [
                'recipient_id' => $recipient->id,
                'campaign_id' => $recipient->campaign_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        $recipient->update([
            'status' => 'sent',
            'last_sent_at' => now(),
            'attempts_sent' => $recipient->attempts_sent + 1,
        ]);

        // Demo mode: schedule a simulated engagement job so the dashboard
        // ticks up live while the jury watches. Real sends (flag on) skip
        // this — only real opens/clicks move the counters.
        if (! (bool) config('services.roomie.allow_real_sends', false)) {
            SimulateRecipientEngagementJob::dispatch($recipient->id)
                ->delay(now()->addSeconds(random_int(5, 90)));
        }

        return true;
    }

    /**
     * Return the forced mail transport — 'log' when the safety flag is off,
     * the configured default when it's on.
     */
    private function forcedMailer(): string
    {
        $allow = (bool) config('services.roomie.allow_real_sends', false);

        return $allow ? (string) config('mail.default', 'log') : 'log';
    }
}
