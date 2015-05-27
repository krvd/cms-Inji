<?php
return [
    'databases' => [
        'local' => [
            'connect_name' => 'база сайта',
            'connect_alias' => 'local',
            'connect_driver' => 'Mysql',
            'connect_options' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'encoding' => 'utf8',
                'db_name' => 'anrim',
                'table_prefix' => 'inji_',
                'port' => '3306',
                'noConnectAbort' => ''
            ]
        ]
    ]
];