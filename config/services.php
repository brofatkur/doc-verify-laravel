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
        'access_key' => env('XENITH_ACCESS_KEY', 'ak-9ec9d28a3464154019f281404d6393b814bb0f14ad2981533999ad7cd22e1b88'),
        'secret_key' => env('XENITH_SECRET_KEY', 'sk-f5d8181853248796c878203d8a276a5bbb4be3a91d422b087dc8e142d2bbe6e9b048e381afd4cd91f2cddad9b785a1ac5503cf98bf70cc1609ccb4af6870656e'),
        'env' => env('XENITH_ENV', 'sandbox'),
    ],

];
