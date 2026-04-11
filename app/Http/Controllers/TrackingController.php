<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Services\Email\EmailTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /** Transparent 1×1 GIF, 43 bytes. */
    private const PIXEL_GIF = 'R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';

    public function __construct(
        private readonly EmailTrackingService $tracking,
    ) {}

    /**
     * Open-pixel handler. Always responds 200 with a 1×1 GIF so broken URLs
     * never leak an error to the recipient's mail client. Token mismatches
     * log a warning but still return the pixel.
     */
    public function open(CampaignRecipient $recipient, string $token): Response
    {
        if (hash_equals($recipient->tracking_token, $token)) {
            if (! $recipient->isUnsubscribed()) {
                $this->tracking->recordOpen($recipient);
            }
        } else {
            Log::warning('TrackingController::open token mismatch', [
                'recipient_id' => $recipient->id,
            ]);
        }

        return response(base64_decode(self::PIXEL_GIF), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
            'Content-Length' => '43',
        ]);
    }

    /**
     * Click redirect handler. Token-only (not signed) because query-string
     * signatures break when the `&` in the href gets HTML-entity-encoded.
     * The click target is a path segment with base64url encoding.
     */
    public function click(CampaignRecipient $recipient, string $token, string $target): RedirectResponse
    {
        abort_unless(hash_equals($recipient->tracking_token, $token), 403);

        $decoded = $this->tracking->decodeTarget($target) ?? url('/');

        if (! $recipient->isUnsubscribed()) {
            $this->tracking->recordClick($recipient);
        }

        return redirect()->away($decoded);
    }

    /**
     * Unsubscribe confirmation page. Two-step flow so email-scanning robots
     * that pre-fetch links can't accidentally opt a user out.
     */
    public function unsubscribe(CampaignRecipient $recipient, string $token): View|Response
    {
        abort_unless(hash_equals($recipient->tracking_token, $token), 403);

        return response()->view('tracking.unsubscribe', [
            'recipient' => $recipient,
            'confirmUrl' => route('tracking.unsubscribe.confirm', [
                'recipient' => $recipient->id,
                'token' => $token,
            ]),
            'alreadyUnsubscribed' => $recipient->isUnsubscribed(),
            'confirmed' => false,
        ]);
    }

    /**
     * Unsubscribe POST. Marks the recipient and inserts the global opt-out.
     * Supports RFC 8058 one-click (Gmail / Apple Mail) via the same route —
     * the CSRF exclusion for `t/u/*` in bootstrap/app.php lets the mail
     * provider POST without a CSRF token.
     */
    public function unsubscribeConfirm(CampaignRecipient $recipient, string $token): View|Response
    {
        abort_unless(hash_equals($recipient->tracking_token, $token), 403);

        if (! $recipient->isUnsubscribed()) {
            $this->tracking->recordUnsubscribe($recipient);
        }

        return response()->view('tracking.unsubscribe', [
            'recipient' => $recipient->fresh(),
            'confirmUrl' => null,
            'alreadyUnsubscribed' => true,
            'confirmed' => true,
        ]);
    }
}
