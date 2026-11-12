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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'company_api' => [
        'url' => env('COMPANY_API_URL'),
        'key' => env('COMPANY_API_KEY'),
        'timeout' => (int) min(5, max(1, (int) env('COMPANY_API_TIMEOUT', 5))),
    ],

    'campus_api' => [
        'base_url' => env('CAMPUS_API_BASE_URL', 'https://bridge.uinssc.ac.id'),
        'bridge_url' => env('BRIDGE_API_URL', env('CAMPUS_API_BASE_URL', 'https://bridge.uinssc.ac.id')),
        'sahaja_url' => env('SAHAJA_API_URL', 'https://sahaja.uinssc.ac.id'),
        'sahaja_key' => env('SAHAJA_API_KEY'),
        'timeout' => (int) min(5, max(1, (int) env('CAMPUS_API_TIMEOUT', 5))),
        'bridge_timeout' => (int) min(5, max(1, (int) env('BRIDGE_API_TIMEOUT', (int) env('CAMPUS_API_TIMEOUT', 5)))),
        'sahaja_timeout' => (int) min(5, max(1, (int) env('SAHAJA_API_TIMEOUT', 5))),
        'connect_timeout' => (float) min(1.0, max(0.1, (float) env('CAMPUS_API_CONNECT_TIMEOUT', 1.0))),
        'verify_ssl' => (bool) env('CAMPUS_API_VERIFY_SSL', true),
        'bridge_verify_ssl' => (bool) env('BRIDGE_API_VERIFY_SSL', (bool) env('CAMPUS_API_VERIFY_SSL', true)),
        'sahaja_verify_ssl' => (bool) env('SAHAJA_API_VERIFY_SSL', (bool) env('CAMPUS_API_VERIFY_SSL', true)),
        'health_cache_ttl' => max(300, (int) env('CAMPUS_API_HEALTH_CACHE_TTL', 300)),
    ],

    'bridge' => [
        'url' => env('BRIDGE_API_URL', env('CAMPUS_API_BASE_URL', 'https://bridge.uinssc.ac.id')),
        'timeout' => (int) min(5, max(1, (int) env('BRIDGE_API_TIMEOUT', (int) env('CAMPUS_API_TIMEOUT', 5)))),
        'connect_timeout' => (float) min(1.0, max(0.1, (float) env('CAMPUS_API_CONNECT_TIMEOUT', 1.0))),
        'verify_ssl' => (bool) env('BRIDGE_API_VERIFY_SSL', (bool) env('CAMPUS_API_VERIFY_SSL', true)),
    ],

    'sahaja' => [
        'url' => env('SAHAJA_API_URL', 'https://sahaja.uinssc.ac.id'),
        'key' => env('SAHAJA_API_KEY'),
        'timeout' => (int) min(5, max(1, (int) env('SAHAJA_API_TIMEOUT', 5))),
        'connect_timeout' => (float) min(1.0, max(0.1, (float) env('CAMPUS_API_CONNECT_TIMEOUT', 1.0))),
        'verify_ssl' => (bool) env('SAHAJA_API_VERIFY_SSL', (bool) env('CAMPUS_API_VERIFY_SSL', true)),
    ],
];
