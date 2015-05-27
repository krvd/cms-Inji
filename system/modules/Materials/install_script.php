<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->create_table('materials_catalogs', array(
        'mc_id' => 'pk',
        'mc_parent_id' => 'int(11) NOT NULL',
        'mc_name' => 'varchar(255) NOT NULL',
        'mc_chpu' => 'varchar(255) NOT NULL',
        'mc_description' => 'text NOT NULL',
        'mc_tree_path' => 'text NOT NULL',
        'mc_user_id' => 'int(11) NOT NULL',
        'mc_image' => 'int(11) NOT NULL',
        'mc_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    App::$cur->db->create_table('materials', array(
        'material_id' => 'pk',
        'material_mc_id' => 'INT(11) NOT NULL',
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
    App::$cur->db->insert('materials', array(
        'material_mc_id' => '0',
        'material_name' => 'Главная',
        'material_chpu' => 'index',
        'material_text' => '<p>Главная страница сайта</p>',
        'material_default' => '1',
        'material_template' => 'current',
        'material_viewer' => 'default',
        'material_preview' => '<p>Главная страница</p>',
        'material_weight' => '0',
        'material_hidden' => '0',
    ));
};
