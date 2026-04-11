<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Services\Email\EmailTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
     * Click redirect handler. Uses the `signed` middleware so tampered URLs
     * 403 automatically; the token is an extra belt-and-braces check.
     * Decodes the base64 target, records the click, and 302s the user.
     */
    public function click(Request $request, CampaignRecipient $recipient, string $token): RedirectResponse
    {
        abort_unless(hash_equals($recipient->tracking_token, $token), 403);

        $encoded = (string) $request->query('u', '');
        $target = $this->decodeTarget($encoded) ?? url('/');

        if (! $recipient->isUnsubscribed()) {
            $this->tracking->recordClick($recipient);
        }

        return redirect()->away($target);
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
            'confirmUrl' => URL::signedRoute('tracking.unsubscribe.confirm', [
                'recipient' => $recipient->id,
                'token' => $token,
            ]),
            'alreadyUnsubscribed' => $recipient->isUnsubscribed(),
            'confirmed' => false,
        ]);
    }

    /**
     * Unsubscribe POST. Marks the recipient and inserts the global opt-out.
     * Supports RFC 8058 one-click (Gmail/Apple Mail) via the same route.
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

    private function decodeTarget(string $encoded): ?string
    {
        if ($encoded === '') {
            return null;
        }

        $padded = strtr($encoded, '-_', '+/');
        $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
        $decoded = base64_decode($padded, true);

        if ($decoded === false) {
            return null;
        }

        if (! preg_match('#^https?://#i', $decoded)) {
            return null;
        }

        return $decoded;
    }
}
