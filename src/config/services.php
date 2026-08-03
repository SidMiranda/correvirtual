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

    'mercadopago' => [
        'token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),

        // TEMPORÁRIO — teste, reverter para $subscription->price quando o Sidney liberar (BUG-001).
        // Enquanto MERCADOPAGO_TEST_PRICE_ENABLED=true, toda cobrança Pix sai no valor de
        // MERCADOPAGO_TEST_PRICE_VALUE, não no preço real do kit. Ver PixController::generatePix.
        'test_price_enabled' => env('MERCADOPAGO_TEST_PRICE_ENABLED', false),
        'test_price_value' => env('MERCADOPAGO_TEST_PRICE_VALUE', 0.05),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

];
