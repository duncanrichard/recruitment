<?php

return [

    'http' => [
        'verify_tls' => env('HTTP_VERIFY_TLS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides
    | the de facto location for this type of information, allowing packages
    | to have a conventional file to locate the various service credentials.
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

    'google_calendar' => [
        'enabled' => env('GOOGLE_CALENDAR_ENABLED', false),
        'credentials' => env('GOOGLE_CALENDAR_CREDENTIALS'),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
        'impersonate_email' => env('GOOGLE_CALENDAR_IMPERSONATE_EMAIL'),
        'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'Asia/Jakarta')),
        'event_duration_minutes' => env('GOOGLE_CALENDAR_EVENT_DURATION_MINUTES', 60),
        'default_location' => env('GOOGLE_CALENDAR_DEFAULT_LOCATION', 'Google Meet'),
        'create_meet' => env('GOOGLE_CALENDAR_CREATE_MEET', true),
    ],

    'fonnte' => [
        'device_url' => env('FONNTE_DEVICE_URL', 'https://api.fonnte.com/device'),
        'connect_timeout' => (int) env('FONNTE_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('FONNTE_TIMEOUT', 20),
    ],

];
