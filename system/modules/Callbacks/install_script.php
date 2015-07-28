<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('callbacks_callback', [
        'callback_id' => 'pk',
        'callback_name' => 'varchar(255) NOT NULL',
        'callback_text' => 'text NOT NULL',
        'callback_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
};
