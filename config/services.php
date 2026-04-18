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

    'pixabay' => [
    'key' => env('PIXABAY_API_KEY'),
    ],
    'pexels' => [
        'key' => env('PEXELS_API_KEY'),
    ],

    'freesound' => [
    'key'       => env('FREESOUND_API_KEY'),
    'client_id' => env('FREESOUND_CLIENT_ID'),
    ],
    'freepik' => [
        'key' => env('FREEPIK_API_KEY'),
    ],
    'iconify' => [
        'base_url' => env('ICONIFY_BASE_URL', 'https://api.iconify.design'),
    ],
    'lottiefiles' => [
        // GraphQL API, tidak butuh key untuk public search
    ],

    // config/services.php
    'ai' => [
        'key'      => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.chatanywhere.tech/v1'),
        'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],
];
