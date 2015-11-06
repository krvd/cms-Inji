<?php

return function ($step = NULL, $params = array()) {

    App::$cur->db->createTable('merchants_pay', [
        'pay_id' => 'pk',
        'pay_callback_module' => 'varchar(255) NOT NULL',
        'pay_callback_method' => 'varchar(255) NOT NULL',
        'pay_data' => 'text NOT NULL',
        'pay_sum' => 'decimal(10, 2) NOT NULL',
        'pay_user_id' => 'INT(11) UNSIGNED NOT NULL',
        'pay_pay_status_id' => 'INT(11) UNSIGNED NOT NULL',
        'pay_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            ]
    );
    App::$cur->db->createTable('merchants_pay_status', [
        'pay_status_id' => 'pk',
        'pay_status_name' => 'varchar(255) NOT NULL',
        'pay_status_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            ]
    );
    $statuses = [
        [
            'pay_status_id' => 1,
            'pay_status_name' => 'Создан'
        ],
        [
            'pay_status_id' => 2,
            'pay_status_name' => 'Оплачен'
        ],
        [
            'pay_status_id' => 3,
            'pay_status_name' => 'Отменен'
        ]
    ];
    foreach ($statuses as $status) {
        App::$cur->db->insert('merchants_pay_status', $status);
    }
    App::$cur->db->createTable('merchants_request', [
        'request_id' => 'pk',
        'request_post' => 'text NOT NULL',
        'request_get' => 'text NOT NULL',
        'request_system' => 'varchar(255) NOT NULL',
        'request_status' => 'varchar(255) NOT NULL',
        'request_result_callback' => 'text NOT NULL',
        'request_pay_id' => 'INT(11) UNSIGNED NOT NULL',
        'request_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('merchants_merchant', [
        'merchant_id' => 'pk',
        'merchant_name' => 'varchar(255) NOT NULL',
        'merchant_image_file_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_active' => 'TINYINT(1) UNSIGNED NOT NULL',
        'merchant_object_name' => 'varchar(255) NOT NULL',
        'request_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    $merchants = [
        [
            'merchant_name' => 'Wallet One',
            'merchant_object_name' => 'WalletOne'
        ],
        [
            'merchant_name' => 'Robokassa',
            'merchant_object_name' => 'Robokassa'
        ],
        [
            'merchant_name' => 'Payeer',
            'merchant_object_name' => 'Payeer'
        ],
    ];
    foreach ($merchants as $merchant) {
        App::$cur->db->insert('merchants_merchant', $merchant);
    }
    App::$cur->db->createTable('merchants_merchant_config', [
        'merchant_config_id' => 'pk',
        'merchant_config_merchant_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_config_name' => 'varchar(255) NOT NULL',
        'merchant_config_value' => 'varchar(255) NOT NULL',
    ]);
};
