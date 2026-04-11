<?php

use App\Services\Webhooks\WebhookSigner;

it('produces a t= and v1= formatted header', function () {
    $secret = 'whsec_test';
    $body = '{"type":"campaign.completed"}';
    $timestamp = 1744080000;

    $header = WebhookSigner::sign($secret, $body, $timestamp);

    expect($header)->toStartWith("t={$timestamp},v1=");
});

it('signs the base string timestamp.body with HMAC-SHA256', function () {
    $secret = 'whsec_known_value';
    $body = 'hello';
    $timestamp = 1744080000;

    $header = WebhookSigner::sign($secret, $body, $timestamp);

    $expected = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);
    expect($header)->toBe("t={$timestamp},v1={$expected}");
});

it('produces different signatures for different bodies', function () {
    $a = WebhookSigner::sign('s', 'body-one', 1000);
    $b = WebhookSigner::sign('s', 'body-two', 1000);

    expect($a)->not->toBe($b);
});
