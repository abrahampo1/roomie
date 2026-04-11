<?php

namespace App\Services\Webhooks;

/**
 * Stripe-style HMAC signer for webhook payloads. The generated header
 * value is `t={timestamp},v1={hmac_sha256_hex}` where the signing base is
 * `{timestamp}.{raw_json_body}`. Consumers reconstruct the same base and
 * compare via constant time, rejecting deliveries older than 5 minutes.
 */
class WebhookSigner
{
    public static function sign(string $secret, string $body, int $timestamp): string
    {
        $base = $timestamp.'.'.$body;
        $hex = hash_hmac('sha256', $base, $secret);

        return "t={$timestamp},v1={$hex}";
    }
}
