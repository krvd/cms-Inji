<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('materials_category', [
        'category_id' => 'pk',
        'category_parent_id' => 'int(11) NOT NULL',
        'category_name' => 'varchar(255) NOT NULL',
        'category_alias' => 'varchar(255) NOT NULL',
        'category_description' => 'text NOT NULL',
        'category_user_id' => 'int(11) NOT NULL',
        'category_image' => 'int(11) NOT NULL',
        'category_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'category_tree_path' => 'text NOT NULL',
    ]);
    App::$cur->db->createTable('materials_material', [
        'material_id' => 'pk',
        'material_category_id' => 'INT(11) NOT NULL',
        'material_user_id' => 'INT(11) NOT NULL',
        'material_name' => 'varchar(255) NOT NULL',
        'material_alias' => 'varchar(255) NOT NULL',
        'material_text' => 'LONGTEXT NOT NULL',
        'material_default' => 'BOOL NOT NULL',
        'material_template' => 'varchar(255) NOT NULL DEFAULT \'current\'',
        'material_viewer' => 'varchar(255) NOT NULL DEFAULT \'default\'',
        'material_preview' => 'text NOT NULL',
        'material_weight' => 'INT(11) NOT NULL',
        'material_image_file_id' => 'INT(11) NOT NULL',
        'material_hidden' => 'BOOL NOT NULL',
        'material_relative_materials_ids' => 'text NOT NULL',
        'material_keywords' => 'text NOT NULL',
        'material_description' => 'text NOT NULL',
        'material_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
        'material_tree_path' => 'text NOT NULL',
    ]);
    App::$cur->db->insert('materials_material', [
        'material_category_id' => '0',
        'material_name' => 'Главная',
        'material_alias' => '',
        'material_text' => '<p>Главная страница сайта</p>',
        'material_default' => '1',
        'material_preview' => '<p>Главная страница</p>',
        'material_tree_path' => '/'
    ]);
};
