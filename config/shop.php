<?php

return [
    'shipping' => [
        'free_threshold' => (float) env('SHOP_FREE_SHIPPING_THRESHOLD', 300000),
        'default_fee'    => (float) env('SHOP_DEFAULT_SHIPPING_FEE', 30000),
    ],

    'vietqr' => [
        'bank_id'      => trim((string) env('SHOP_VIETQR_BANK_ID', '')),
        'bank_name'    => trim((string) env('SHOP_VIETQR_BANK_NAME', '')),
        'account_no'   => trim((string) env('SHOP_VIETQR_ACCOUNT_NO', '')),
        'account_name' => trim((string) env('SHOP_VIETQR_ACCOUNT_NAME', '')),
        'template'     => trim((string) env('SHOP_VIETQR_TEMPLATE', 'compact2')),
    ],

    'payos' => [
        'client_id'    => trim((string) env('PAYOS_CLIENT_ID', '')),
        'api_key'      => trim((string) env('PAYOS_API_KEY', '')),
        'checksum_key' => trim((string) env('PAYOS_CHECKSUM_KEY', '')),
        'base_url'     => rtrim((string) env('PAYOS_BASE_URL', 'https://api-merchant.payos.vn'), '/'),
    ],

    'contact' => [
        'phone'   => env('SHOP_CONTACT_PHONE', '0969 376 447'),
        'email'   => env('SHOP_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@shophandmade.vn')),
        'address' => env('SHOP_CONTACT_ADDRESS', 'Lê Trọng Tấn, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh'),
        'hours'   => env('SHOP_CONTACT_HOURS', 'Thứ 2 – Chủ nhật: 08:30 – 21:30'),
    ],
];
