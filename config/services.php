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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'mailjet' => [
        'key' => env('MAILJET_APIKEY'),
        'secret' => env('MAILJET_APISECRET'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'recaptcha' => [
        'secretkey' => env('CAPTCHA_SECRET_KEY'),
        'sitekey' => env('CAPTCHA_SITE_KEY'),
        'enabled' => env('CAPTCHA_ENABLED'),
    ],

    'sysmailing' => [
        'mailonlogin' => env('MAIL_ON_LOGIN'),
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],

    'delhivery' => [
        'key' => env('DELHIVERY_KEY'),
        'sandbox' => env('DELHIVERY_SANDBOX', true),
    ],

    'ekart' => [
        'client_id' => env('EKART_CLIENT_ID'),
        'username' => env('EKART_USERNAME'),
        'password' => env('EKART_PASSWORD'),
    ],

    'shipping' => [
        'providers' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('SHIPPING_PROVIDERS', 'delhivery,ekart'))
        ))),
    ],

    'sms' => [
        'default' => env('SMS_PROVIDER', 'smsfortius'),
        'enabled' => env('SMS_ENABLED', true),
    ],

    'smsfortius' => [
        'base_url' => env('SMSFORTIUS_BASE_URL'),
        'apikey' => env('SMSFORTIUS_API_KEY'),
        'senderid' => env('SMSFORTIUS_SENDER_ID'),
        'country_code' => env('SMSFORTIUS_COUNTRY_CODE', '91'),
        'timeout' => env('SMSFORTIUS_TIMEOUT', 10),
    ],
];
