<?php

namespace App\Services\Webhooks;

/**
 * Canonical registry of webhook event types. Any event emitted from the
 * pipeline / send subsystem / tracking service must be listed here, and
 * any event a user subscribes to is validated against this list.
 */
class WebhookEvents
{
    public const CAMPAIGN_EVENTS = [
        'campaign.created',
        'campaign.analysis_completed',
        'campaign.strategy_completed',
        'campaign.creative_completed',
        'campaign.audit_completed',
        'campaign.completed',
        'campaign.failed',
        'campaign.creative_refined',
        'campaign.send_started',
        'campaign.followup_started',
    ];

    public const RECIPIENT_EVENTS = [
        'recipient.sent',
        'recipient.bounced',
        'recipient.opened',
        'recipient.clicked',
        'recipient.converted',
        'recipient.unsubscribed',
    ];

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(array_merge(self::CAMPAIGN_EVENTS, self::RECIPIENT_EVENTS));
    }

    /**
     * Validates a user-supplied `events` array. Returns the normalized
     * value — either `["*"]` or the subset of known events — or null if
     * the input is invalid.
     *
     * @param  mixed  $input
     * @return array<int, string>|null
     */
    public static function normalize($input): ?array
    {
        if (! is_array($input) || $input === []) {
            return null;
        }

        if (in_array('*', $input, true)) {
            return ['*'];
        }

        $known = self::all();
        $clean = [];

        foreach ($input as $event) {
            if (! is_string($event)) {
                return null;
            }

            if (! in_array($event, $known, true)) {
                return null;
            }

            $clean[] = $event;
        }

        return array_values(array_unique($clean));
    }
}
