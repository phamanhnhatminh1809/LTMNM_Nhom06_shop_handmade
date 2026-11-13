<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bảng cấu hình hằng số cho các nút thao tác dạng icon trong bảng dữ liệu
    |--------------------------------------------------------------------------
    */
    'table_actions' => [
        'view' => [
            'icon'    => 'bi-eye',
            'variant' => 'view',
            'label'   => 'Xem chi tiết',
        ],
        'info' => [
            'icon'    => 'bi-info-circle',
            'variant' => 'view',
            'label'   => 'Thông tin chi tiết',
        ],
        'store' => [
            'icon'    => 'bi-box-arrow-up-right',
            'variant' => 'store',
            'label'   => 'Xem ngoài cửa hàng',
        ],
        'edit' => [
            'icon'    => 'bi-pencil',
            'variant' => 'edit',
            'label'   => 'Chỉnh sửa',
        ],
        'delete' => [
            'icon'    => 'bi-trash3',
            'variant' => 'danger',
            'label'   => 'Xóa',
        ],
        'cancel' => [
            'icon'    => 'bi-x-lg',
            'variant' => 'danger',
            'label'   => 'Hủy',
        ],
        'approve' => [
            'icon'    => 'bi-check2',
            'variant' => 'success',
            'label'   => 'Xác nhận',
        ],
    ],
];
