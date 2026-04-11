<?php

namespace App\Services\Email;

use App\Http\Resources\CampaignRecipientResource;
use App\Models\CampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\Request;

class EmailTrackingService
{
    /**
     * Open-pixel URL. Token-only (no `signed` middleware): Gmail and Apple
     * Mail Privacy Protection pre-fetch images via proxies that strip query
     * strings, so signed URLs false-negative on every open.
     */
    public function openPixelUrl(CampaignRecipient $recipient): string
    {
        return route('tracking.open', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
        ]);
    }

    /**
     * Click redirect URL. The target URL is base64url-encoded as a path
     * segment (not a query param) so HTML entity encoding of `&` doesn't
     * corrupt the URL when copy-pasted from a log or rendered by a permissive
     * mail client. Security comes from the per-recipient 40-char
     * `tracking_token` — 240 bits of entropy, effectively unguessable.
     */
    public function clickUrl(CampaignRecipient $recipient, string $target): string
    {
        return route('tracking.click', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
            'target' => $this->encodeTarget($target),
        ]);
    }

    /**
     * Unsubscribe URL. Token-only for the same reason as the click URL.
     */
    public function unsubscribeUrl(CampaignRecipient $recipient): string
    {
        return route('tracking.unsubscribe', [
            'recipient' => $recipient->id,
            'token' => $recipient->tracking_token,
        ]);
    }

    public function encodeTarget(string $target): string
    {
        return rtrim(strtr(base64_encode($target), '+/', '-_'), '=');
    }

    public function decodeTarget(string $encoded): ?string
    {
        if ($encoded === '') {
            return null;
        }

        $padded = strtr($encoded, '-_', '+/');
        $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
        $decoded = base64_decode($padded, true);

        if ($decoded === false || ! preg_match('#^https?://#i', $decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Record one open against a recipient. Idempotent in the sense that
     * multiple opens simply increment the counter.
     */
    public function recordOpen(CampaignRecipient $recipient): void
    {
        $wasFirst = $recipient->first_opened_at === null;

        $update = [
            'opens_count' => $recipient->opens_count + 1,
            'last_opened_at' => now(),
        ];

        if ($wasFirst) {
            $update['first_opened_at'] = now();
        }

        $recipient->update($update);

        // Only fire the webhook on the FIRST open so downstream systems
        // don't get spammed every time a mail client re-fetches the pixel.
        if ($wasFirst) {
            WebhookDispatcher::dispatchRecipientEvent($recipient->fresh(), 'recipient.opened', [
                'recipient' => (new CampaignRecipientResource($recipient->fresh()))->toArray(new Request),
            ]);
        }
    }

    /**
     * Record one click against a recipient and mark them as converted
     * (per design: the first click stops the follow-up loop for that
     * recipient).
     */
    public function recordClick(CampaignRecipient $recipient): void
    {
        $wasFirstClick = $recipient->first_clicked_at === null;
        $wasFirstConversion = $recipient->converted_at === null;

        $update = [
            'clicks_count' => $recipient->clicks_count + 1,
            'last_clicked_at' => now(),
        ];

        if ($wasFirstClick) {
            $update['first_clicked_at'] = now();
        }

        if ($wasFirstConversion) {
            $update['converted_at'] = now();
            $update['status'] = 'converted';
        }

        $recipient->update($update);

        if ($wasFirstClick) {
            WebhookDispatcher::dispatchRecipientEvent($recipient->fresh(), 'recipient.clicked', [
                'recipient' => (new CampaignRecipientResource($recipient->fresh()))->toArray(new Request),
            ]);
        }

        if ($wasFirstConversion) {
            WebhookDispatcher::dispatchRecipientEvent($recipient->fresh(), 'recipient.converted', [
                'recipient' => (new CampaignRecipientResource($recipient->fresh()))->toArray(new Request),
            ]);
        }
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

        WebhookDispatcher::dispatchRecipientEvent($recipient->fresh(), 'recipient.unsubscribed', [
            'recipient' => (new CampaignRecipientResource($recipient->fresh()))->toArray(new Request),
        ]);
    }
}
