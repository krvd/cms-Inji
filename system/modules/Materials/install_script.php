<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('materials_catalog', array(
        'catalog_id' => 'pk',
        'catalog_parent_id' => 'int(11) NOT NULL',
        'catalog_name' => 'varchar(255) NOT NULL',
        'catalog_chpu' => 'varchar(255) NOT NULL',
        'catalog_description' => 'text NOT NULL',
        'catalog_tree_path' => 'text NOT NULL',
        'catalog_user_id' => 'int(11) NOT NULL',
        'catalog_image' => 'int(11) NOT NULL',
        'catalog_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    App::$cur->db->createTable('materials_material', array(
        'material_id' => 'pk',
        'material_catalog_id' => 'INT(11) NOT NULL',
        'material_name' => 'varchar(255) NOT NULL',
        'material_chpu' => 'varchar(255) NOT NULL',
        'material_text' => 'LONGTEXT NOT NULL',
        'material_default' => 'BOOL NOT NULL',
        'material_template' => 'varchar(255) NOT NULL',
        'material_tooltip' => 'varchar(255) NOT NULL',
        'material_viewer' => 'varchar(255) NOT NULL',
        'material_preview' => 'text NOT NULL',
        'material_weight' => 'INT(11) NOT NULL',
        'material_hidden' => 'BOOL NOT NULL',
        'material_nexts' => 'text NOT NULL',
        'material_keywords' => 'text NOT NULL',
        'material_tree_path' => 'text NOT NULL',
        'material_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    App::$cur->db->insert('materials_material', array(
        'material_catalog_id' => '0',
        'material_name' => 'Главная',
        'material_chpu' => 'index',
        'material_text' => '<p>Главная страница сайта</p>',
        'material_default' => '1',
        'material_template' => 'current',
        'material_viewer' => 'default',
        'material_preview' => '<p>Главная страница</p>',
        'material_weight' => '0',
        'material_hidden' => '0',
        'material_tree_path' => '/'
    ));
};
