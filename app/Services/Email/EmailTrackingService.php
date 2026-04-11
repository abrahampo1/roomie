<?php

namespace App\Services\Email;

use App\Models\CampaignRecipient;
use App\Models\EmailUnsubscribe;
use Illuminate\Support\Facades\URL;

class EmailTrackingService
{
    /**
     * Open-pixel URL. Deliberately NOT signed because Gmail/Apple Mail
     * Privacy Protection fetch images via proxies that strip query strings,
     * which would false-negative the signature on every open. We rely on the
     * per-recipient `tracking_token` for tamper protection instead.
     */
    public function openPixelUrl(CampaignRecipient $recipient): string
    {
        return route('tracking.open', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
        ]);
    }

    /**
     * Click redirect URL. Signed because real humans click it directly and
     * we want to 403 any tampered click. The original target is base64-encoded
     * so UTM parameters and other query strings survive byte-for-byte.
     */
    public function clickUrl(CampaignRecipient $recipient, string $target): string
    {
        return URL::signedRoute('tracking.click', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
            'u' => rtrim(strtr(base64_encode($target), '+/', '-_'), '='),
        ]);
    }

    /**
     * Unsubscribe URL. Signed and used both in the visible email footer and
     * in the `List-Unsubscribe` header for one-click support.
     */
    public function unsubscribeUrl(CampaignRecipient $recipient): string
    {
        return URL::signedRoute('tracking.unsubscribe', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
        ]);
    }

    /**
     * Record one open against a recipient. Idempotent in the sense that
     * multiple opens simply increment the counter.
     */
    public function recordOpen(CampaignRecipient $recipient): void
    {
        $update = [
            'opens_count' => $recipient->opens_count + 1,
            'last_opened_at' => now(),
        ];

        if ($recipient->first_opened_at === null) {
            $update['first_opened_at'] = now();
        }

        $recipient->update($update);
    }

    /**
     * Record one click against a recipient and mark them as converted
     * (per design: the first click stops the follow-up loop for that
     * recipient).
     */
    public function recordClick(CampaignRecipient $recipient): void
    {
        $update = [
            'clicks_count' => $recipient->clicks_count + 1,
            'last_clicked_at' => now(),
        ];

        if ($recipient->first_clicked_at === null) {
            $update['first_clicked_at'] = now();
        }

        if ($recipient->converted_at === null) {
            $update['converted_at'] = now();
            $update['status'] = 'converted';
        }

        $recipient->update($update);
    }

    /**
     * Mark the recipient unsubscribed and add the address to the global
     * opt-out list so future campaigns never reach them either.
     */
    public function recordUnsubscribe(CampaignRecipient $recipient): void
    {
        $recipient->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        EmailUnsubscribe::query()->updateOrCreate(
            ['email' => $recipient->email],
            [
                'source_campaign_id' => $recipient->campaign_id,
                'reason' => 'user_clicked',
            ],
        );
    }
}
