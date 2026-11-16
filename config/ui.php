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

    /*
    |--------------------------------------------------------------------------
    | Quy chuẩn hằng số cho các nút hành động dùng chung (<x-button>)
    |--------------------------------------------------------------------------
    */
    'buttons' => [
        'admin' => [
            'primary'        => ['class' => 'btn-modal-save', 'icon' => 'bi-check-lg'],
            'secondary'      => ['class' => 'btn-secondary-outline', 'icon' => null],
            'cancel'         => ['class' => 'btn-modal-cancel', 'icon' => null],
            'danger'         => ['class' => 'btn-inline-action danger', 'icon' => 'bi-trash3'],
            'success'        => ['class' => 'btn-inline-action success', 'icon' => 'bi-check2-circle'],
            'inline_primary' => ['class' => 'btn-inline-action primary', 'icon' => null],
            'add'            => ['class' => 'btn-add-product', 'icon' => 'bi-plus-lg'],
        ],
        'shop' => [
            'primary'   => ['class' => 'btn btn--primary', 'icon' => null],
            'secondary' => ['class' => 'btn btn--secondary', 'icon' => null],
            'outline'   => ['class' => 'btn btn--outline', 'icon' => null],
            'ghost'     => ['class' => 'btn btn--ghost', 'icon' => null],
            'cancel'    => ['class' => 'btn-cancel-modal', 'icon' => null],
            'danger'    => ['class' => 'btn-confirm-modal', 'icon' => null],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quy chuẩn thông báo phản hồi (<x-alert> & Toast)
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'auto_dismiss_ms' => 4000,
        'types' => [
            'success' => ['icon' => 'bi-check-circle-fill', 'admin_class' => 'success', 'shop_class' => 'is-success'],
            'error'   => ['icon' => 'bi-exclamation-octagon-fill', 'admin_class' => 'error', 'shop_class' => 'is-error'],
            'warning' => ['icon' => 'bi-exclamation-triangle-fill', 'admin_class' => 'info', 'shop_class' => 'is-warning'],
            'info'    => ['icon' => 'bi-info-circle-fill', 'admin_class' => 'info', 'shop_class' => 'is-info'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quy chuẩn Dropdown chọn dùng chung (<x-select>)
    |--------------------------------------------------------------------------
    */
    'select' => [
        'chevron_icon' => 'bi-chevron-down',
        'check_icon'   => 'bi-check2',
        'empty_text'   => 'Không có lựa chọn phù hợp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Quy chuẩn Modal xác nhận dùng chung toàn Admin (#adminConfirmModal)
    |--------------------------------------------------------------------------
    */
    'confirm_variants' => [
        'primary' => [
            'icon'          => 'bi-truck',
            'title'         => 'Xác nhận thao tác',
            'confirm_label' => 'Xác nhận',
            'confirm_class' => 'btn-modal-save',
        ],
        'success' => [
            'icon'          => 'bi-check2-circle',
            'title'         => 'Xác nhận hoàn tất',
            'confirm_label' => 'Đồng ý',
            'confirm_class' => 'btn-confirm-modal-success',
        ],
        'danger' => [
            'icon'          => 'bi-exclamation-triangle-fill',
            'title'         => 'Xác nhận thao tác',
            'confirm_label' => 'Đồng ý thực hiện',
            'confirm_class' => 'btn-confirm-modal-danger',
        ],
    ],
];
