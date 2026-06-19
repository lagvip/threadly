<?php

return [
    'checkout' => [
        'cart_session_key' => 'threadly.checkout.cart_item_ids',
        'buy_now_session_key' => 'threadly.checkout.buy_now',
        'voucher_session_key' => 'threadly.checkout.voucher',
    ],

    'view_composers' => [
        'header' => [
            'client.partials.header',
            'client.partials.footer',
        ],
    ],

    'integrations' => [
        'log_payloads' => env('THREADLY_LOG_INTEGRATION_PAYLOADS', false),
    ],
];
