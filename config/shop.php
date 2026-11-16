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

    'ai' => [
        'api_key'         => trim((string) env('GEMINI_API_KEY', '')),
        'model'           => trim((string) env('GEMINI_MODEL', 'gemini-3.5-flash')),
        'fallback_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GEMINI_FALLBACK_MODELS', 'gemini-3.5-flash-lite,gemini-3.1-flash-lite,gemini-2.5-flash,gemini-2.5-flash-lite'))
        ))),
        'base_url'        => rtrim((string) env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
        'timeout'         => max(5, (int) env('GEMINI_TIMEOUT', 20)),
        'refusal_message' => env(
            'SHOP_AI_REFUSAL_MESSAGE',
            'Xin lỗi bạn, mình là trợ lý AI của Shop Handmade nên chỉ có thể tư vấn và gợi ý các sản phẩm thủ công hoặc thông tin có sẵn trong hệ thống của cửa hàng. Bạn hãy thử hỏi mình về các món đồ handmade như thú bông len, móc khóa, túi xách, nến thơm hoặc quà tặng nhé!'
        ),
    ],
];
