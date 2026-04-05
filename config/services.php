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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'ghn' => [
        'base_url'            => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api'),
        'token'               => env('GHN_TOKEN'),
        'shop_id'             => env('GHN_SHOP_ID'),
        'from_district_id'    => env('GHN_FROM_DISTRICT_ID'),
        'from_ward_code'      => env('GHN_FROM_WARD_CODE'),
        'from_name'           => env('GHN_FROM_NAME'),
        'from_phone'          => env('GHN_FROM_PHONE'),
        'from_address'        => env('GHN_FROM_ADDRESS'),
        'from_ward_name'      => env('GHN_FROM_WARD_NAME'),
        'from_district_name'  => env('GHN_FROM_DISTRICT_NAME'),
        'from_province_name'  => env('GHN_FROM_PROVINCE_NAME'),
    ],
    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL'),
    ],
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3-flash-preview'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],
];
