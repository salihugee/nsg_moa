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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'azure' => [
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI'),
        'powerbi' => [
            'workspace_id' => env('POWERBI_WORKSPACE_ID'),
            'dataset_id' => env('POWERBI_DATASET_ID'),
            'report_id' => env('POWERBI_REPORT_ID'),
            'scope' => env('POWERBI_SCOPE', 'https://analysis.windows.net/powerbi/api/.default'),
        ]
    ],

    'weather' => [
        'api_key' => env('WEATHER_API_KEY'),
        'base_url' => env('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5'),
        'cache_duration' => env('WEATHER_CACHE_DURATION', 1800), // 30 minutes
    ],

    'maps' => [
        'provider' => env('MAPS_PROVIDER', 'google'),
        'google_maps_key' => env('GOOGLE_MAPS_API_KEY'),
        'geocoding_endpoint' => env('GEOCODING_API_ENDPOINT'),
    ],

    'sms' => [
        'url' => env('SMS_API_URL', 'https://api.sms.gateway.com'),
        'key' => env('SMS_API_KEY'),
        'sender' => env('SMS_SENDER_ID', 'NSG-MOA'),
        'timeout' => env('SMS_TIMEOUT', 30),
        'retry_attempts' => env('SMS_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('SMS_RETRY_DELAY', 5),
        'webhook_url' => env('SMS_WEBHOOK_URL'),
        'delivery_status_endpoint' => env('SMS_STATUS_ENDPOINT'),
    ],

    'monitoring' => [
        'log_channel' => env('MONITORING_LOG_CHANNEL', 'stack'),
        'notification_email' => env('MONITORING_EMAIL'),
        'alert_threshold' => env('MONITORING_ALERT_THRESHOLD', 90), // percentage
        'metrics_retention_days' => env('METRICS_RETENTION_DAYS', 30),
    ],
];
