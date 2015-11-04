<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('callbacks_callback', [
        'callback_id' => 'pk',
        'callback_name' => 'varchar(255) NOT NULL',
        'callback_profession' => 'varchar(255) NOT NULL',
        'callback_mail' => 'varchar(255) NOT NULL',
        'callback_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_callback_type_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_user_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_text' => 'text NOT NULL',
        'callback_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('callbacks_callback_type', [
        'callback_type_id' => 'pk',
        'callback_type_name' => 'varchar(255) NOT NULL',
        'callback_type_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->insert('callbacks_callback_type', [
        'callback_type_name' => 'Положительный'
    ]);
    App::$cur->db->insert('callbacks_callback_type', [
        'callback_type_name' => 'Нейтральный'
    ]);
    App::$cur->db->insert('callbacks_callback_type', [
        'callback_type_name' => 'Отрицательный'
    ]);
};
