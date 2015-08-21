<?php

return function ($step = NULL, $params = array()) {
    \App::$cur->db->createTable('exchange1c_exchange', [
        'exchange_id' => 'pk',
        'exchange_type' => 'varchar(255) NOT NULL',
        'exchange_session' => 'varchar(255) NOT NULL',
        'exchange_path' => 'text NOT NULL',
        'exchange_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'exchange_date_end' => 'timestamp NOT NULL DEFAULT 0',
    ]);
    \App::$cur->db->createTable('exchange1c_exchange_file', [
        'exchange_file_id' => 'pk',
        'exchange_file_exchange_id' => 'int(11) UNSIGNED NOT NULL',
        'exchange_file_name' => 'text NOT NULL',
        'exchange_file_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    \App::$cur->db->createTable('exchange1c_exchange_log', [
        'exchange_log_id' => 'pk',
        'exchange_log_type' => 'varchar(255) NOT NULL',
        'exchange_log_info' => 'text NOT NULL',
        'exchange_log_status' => 'varchar(255) NOT NULL',
        'exchange_log_exchange_id' => 'int(11) UNSIGNED NOT NULL',
        'exchange_log_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'exchange_log_date_end' => 'timestamp NOT NULL DEFAULT 0',
    ]);
};
