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

    'ipaymu' => [
        'va' => env('IPAYMU_VA', ''),
        'api_key' => env('IPAYMU_API_KEY', ''),
        'env' => env('IPAYMU_ENV', 'sandbox'),
    ],

    'xenith' => [
        'access_key' => env('XENITH_ACCESS_KEY', 'ak-6b4c740dae79be46ee0189169e6636fd41917774f4365a4025529300c731bd2b'),
        'secret_key' => env('XENITH_SECRET_KEY', 'sk-2cc25d1dd0b624c03650712be4749eea1686ce4fb30690a6316dee7ef96879896f9a19fc211c9e7fd429771f888a422099f01e9bc34c0fbeac4b07ab9d9f799e'),
        'webhook_secret' => env('XENITH_WEBHOOK_SECRET', 'tqYxuHTdCIRApkXloJviGV0l5aBcMSMhf8K05nvgFXfEMs7-Xw0D1lV79V_PJt3Q'),
        'env' => env('XENITH_ENV', 'production'),
    ],

];
