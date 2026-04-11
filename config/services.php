<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'roomie' => [
        // When false (the default), CampaignSender forces the 'log' mailer
        // regardless of MAIL_MAILER — nothing leaves the host, the demo is
        // safe by default. Flip to true only when you have consent from the
        // recipients and a real MAIL_MAILER configured.
        'allow_real_sends' => env('ROOMIE_ALLOW_REAL_SENDS', false),

        // Hard cap on how long the user's API key may remain encrypted on
        // the campaign row for autonomous follow-ups.
        'followup_max_retention_days' => env('ROOMIE_FOLLOWUP_MAX_RETENTION_DAYS', 14),
    ],

];
