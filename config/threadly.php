<?php

return [
    'checkout' => [
        'cart_session_key' => 'checkout_selected_items',
        'buy_now_session_key' => 'buy_now_checkout',
        'voucher_session_key' => 'checkout_voucher',
    ],

    'view_composers' => [
        'header' => [
            'client.partials.header',
            'client.partials.footer',
        ],
    ],
];
