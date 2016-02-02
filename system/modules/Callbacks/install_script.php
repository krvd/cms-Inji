<?php

return function ($step = NULL, $params = []) {
    //Категории
    App::$cur->db->createTable('callbacks_category', [
        'category_id' => 'pk',
        //Основные параметры
        'category_parent_id' => 'int(11) UNSIGNED NOT NULL',
        'category_name' => 'varchar(255) NOT NULL',
        'category_alias' => 'varchar(255) NOT NULL',
        'category_viewer' => 'varchar(255) NOT NULL',
        'category_template' => 'varchar(255) NOT NULL',
        'category_description' => 'text NOT NULL',
        'category_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        'category_options_inherit' => 'bool NOT NULL',
        //Системные
        'category_tree_path' => 'text NOT NULL',
        'category_user_id' => 'int(11) UNSIGNED NOT NULL',
        'category_weight' => 'int(11) UNSIGNED NOT NULL',
        'category_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ], [
        'INDEX ' . App::$cur->db->table_prefix . '_callbacks_category_category_parent_id (category_parent_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_callbacks_category_category_tree_path (category_tree_path(255))',
    ]);
    App::$cur->db->createTable('callbacks_callback', [
        'callback_id' => 'pk',
        'callback_name' => 'varchar(255) NOT NULL',
        'callback_profession' => 'varchar(255) NOT NULL',
        'callback_phone' => 'varchar(255) NOT NULL',
        'callback_mail' => 'varchar(255) NOT NULL',
        'callback_youtube_url' => 'varchar(255) NOT NULL',
        'callback_view' => 'tinyint(1) UNSIGNED NOT NULL',
        'callback_category_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_callback_type_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_user_id' => 'int(11) UNSIGNED NOT NULL',
        'callback_original_url' => 'text NOT NULL',
        'callback_text' => 'text NOT NULL',
        'callback_weight' => 'int(11) UNSIGNED NOT NULL',
        'callback_tree_path' => 'TEXT NOT NULL',
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
