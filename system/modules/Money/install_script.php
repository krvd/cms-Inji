<?php

return function ($step = NULL, $params = []) {

    App::$cur->db->createTable('money_currency', [
        'currency_id' => 'pk',
        'currency_name' => 'varchar(255) NOT NULL',
        'currency_code' => 'varchar(255) NOT NULL',
        'currency_refill' => 'tinyint(1) UNSIGNED NOT NULL',
        'currency_wallet' => 'tinyint(1) UNSIGNED NOT NULL',
        'currency_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    $currencies = [
        [
            'currency_name' => 'Доллары',
            'currency_code' => '$'
        ],
        [
            'currency_name' => 'Рубли',
            'currency_code' => '₽'
        ],
    ];


    App::$cur->db->createTable('money_currency_exchangerate', [
        'currency_exchangerate_id' => 'pk',
        'currency_exchangerate_currency_id' => 'int(11) UNSIGNED NOT NULL',
        'currency_exchangerate_target_currency_id' => 'int(11) UNSIGNED NOT NULL',
        'currency_exchangerate_rate' => 'decimal(10,4) UNSIGNED NOT NULL',
        'currency_exchangerate_date_modify' => 'timestamp NOT NULL DEFAULT 0',
        'currency_exchangerate_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_currency_exchangerate_history', [
        'currency_exchangerate_history_id' => 'pk',
        'currency_exchangerate_history_currency_exchangerate_id' => 'int(11) UNSIGNED NOT NULL',
        'currency_exchangerate_history_old' => 'decimal(10,4) UNSIGNED NOT NULL',
        'currency_exchangerate_history_new' => 'decimal(10,4) UNSIGNED NOT NULL',
        'currency_exchangerate_history_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_wallet', [
        'wallet_id' => 'pk',
        'wallet_currency_id' => 'int(11) UNSIGNED NOT NULL',
        'wallet_user_id' => 'int(11) UNSIGNED NOT NULL',
        'wallet_amount' => 'decimal(10,4) NOT NULL',
        'wallet_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_wallet_history', [
        'wallet_history_id' => 'pk',
        'wallet_history_wallet_id' => 'int(11) UNSIGNED NOT NULL',
        'wallet_history_old' => 'decimal(10,4) NOT NULL',
        'wallet_history_new' => 'decimal(10,4) NOT NULL',
        'wallet_history_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);

    App::$cur->db->createTable('money_pay', [
        'pay_id' => 'pk',
        'pay_callback_module' => 'varchar(255) NOT NULL',
        'pay_callback_method' => 'varchar(255) NOT NULL',
        'pay_data' => 'text NOT NULL',
        'pay_sum' => 'decimal(10, 2) NOT NULL',
        'pay_description' => 'varchar(255) NOT NULL',
        'pay_type' => 'varchar(255) NOT NULL',
        'pay_user_id' => 'INT(11) UNSIGNED NOT NULL',
        'pay_currency_id' => 'INT(11) UNSIGNED NOT NULL',
        'pay_pay_status_id' => 'INT(11) UNSIGNED NOT NULL DEFAULT 1',
        'pay_date_recive' => 'timestamp DEFAULT 0',
        'pay_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_pay_status', [
        'pay_status_id' => 'pk',
        'pay_status_name' => 'varchar(255) NOT NULL',
        'pay_status_code' => 'varchar(255) NOT NULL',
        'pay_status_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    $statuses = [
        [
            'pay_status_id' => 1,
            'pay_status_name' => 'Создан',
            'pay_status_code' => 'new'
        ],
        [
            'pay_status_id' => 2,
            'pay_status_name' => 'Оплачен',
            'pay_status_code' => 'success'
        ],
        [
            'pay_status_id' => 3,
            'pay_status_name' => 'Отменен',
            'pay_status_code' => 'cancel'
        ],
        [
            'pay_status_id' => 4,
            'pay_status_name' => 'Ошибка',
            'pay_status_code' => 'error'
        ]
    ];
    foreach ($statuses as $status) {
        App::$cur->db->insert('money_pay_status', $status);
    }

    App::$cur->db->createTable('money_merchant', [
        'merchant_id' => 'pk',
        'merchant_name' => 'varchar(255) NOT NULL',
        'merchant_image_file_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_active' => 'TINYINT(1) UNSIGNED NOT NULL',
        'merchant_pay' => 'TINYINT(1) UNSIGNED NOT NULL',
        'merchant_refill' => 'TINYINT(1) UNSIGNED NOT NULL',
        'merchant_object_name' => 'varchar(255) NOT NULL',
        'merchant_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_merchant_request', [
        'merchant_request_id' => 'pk',
        'merchant_request_post' => 'text NOT NULL',
        'merchant_request_get' => 'text NOT NULL',
        'merchant_request_merchant_id' => 'int(11) UNSIGNED NOT NULL',
        'merchant_request_system' => 'varchar(255) NOT NULL',
        'merchant_request_status' => 'varchar(255) NOT NULL',
        'merchant_request_result_callback' => 'text NOT NULL',
        'merchant_request_pay_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_request_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('money_merchant_config', [
        'merchant_config_id' => 'pk',
        'merchant_config_merchant_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_config_name' => 'varchar(255) NOT NULL',
        'merchant_config_value' => 'varchar(255) NOT NULL',
    ]);
    App::$cur->db->createTable('money_merchant_currency', [
        'merchant_currency_id' => 'pk',
        'merchant_currency_merchant_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_currency_currency_id' => 'INT(11) UNSIGNED NOT NULL',
        'merchant_currency_code' => 'varchar(255) NOT NULL',
    ]);
    $merchants = [
        [
            'merchant_name' => 'Wallet One',
            'merchant_object_name' => 'WalletOne',
            'merchant_image_file_id' => App::$cur->db->insert('files_file', [
                'file_path' => '/static/moduleAsset/Money/images/WalletOne.png',
                'file_type' => 1
            ])
        ],
        [
            'merchant_name' => 'Robokassa',
            'merchant_object_name' => 'Robokassa',
            'merchant_image_file_id' => App::$cur->db->insert('files_file', [
                'file_path' => '/static/moduleAsset/Money/images/Robokassa.png',
                'file_type' => 1
            ])
        ],
        [
            'merchant_name' => 'Payeer',
            'merchant_object_name' => 'Payeer',
            'merchant_image_file_id' => App::$cur->db->insert('files_file', [
                'file_path' => '/static/moduleAsset/Money/images/Payeer.png',
                'file_type' => 1
            ])
        ],
        [
            'merchant_name' => 'PerfectMoney',
            'merchant_object_name' => 'PerfectMoney',
            'merchant_image_file_id' => App::$cur->db->insert('files_file', [
                'file_path' => '/static/moduleAsset/Money/images/PerfectMoney.png',
                'file_type' => 1
            ])
        ],
    ];
    $merchantsConfig = [
        [
            [
                'merchant_config_name' => 'shopId',
                'merchant_config_value' => ''
            ],
            [
                'merchant_config_name' => 'secret',
                'merchant_config_value' => ''
            ]
        ],
        [
            [
                'merchant_config_name' => 'login',
                'merchant_config_value' => ''
            ],
            [
                'merchant_config_name' => 'pass1',
                'merchant_config_value' => ''
            ],
            [
                'merchant_config_name' => 'pass2',
                'merchant_config_value' => ''
            ]
        ],
        [
            [
                'merchant_config_name' => 'shopId',
                'merchant_config_value' => ''
            ],
            [
                'merchant_config_name' => 'secret',
                'merchant_config_value' => ''
            ]
        ],
    ];
    $merchantsCurrencies = [
        [
            [
                'merchant_currency_currency_id' => 2,
                'merchant_currency_code' => '643'
            ],
            [
                'merchant_currency_currency_id' => 2,
                'merchant_currency_code' => 'RUB'
            ],
            [
            ],
            [
                'merchant_currency_currency_id' => 1,
                'merchant_currency_code' => 'USD'
            ],
        ]
    ];
    foreach ($merchants as $key => $merchant) {
        $id = App::$cur->db->insert('money_merchant', $merchant);
        foreach ($merchantsConfig[$key] as $config) {
            $config['merchant_config_merchant_id'] = $id;
            App::$cur->db->insert('money_merchant_config', $config);
        }
        foreach ($merchantsCurrencies[$key] as $currency) {
            $currency['merchant_currency_merchant_id'] = $id;
            App::$cur->db->insert('money_merchant_currency', $currency);
        }
    }
    Inji::$inst->listen('ecommerceCartClosed', 'Money-rewardTrigger', [
        'module' => 'Money',
        'method' => 'rewardTrigger'
            ], true);
};
