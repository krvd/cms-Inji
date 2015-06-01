<?php
return function($step = NULL, $params = array()) {

    App::$cur->db->createTable('files_file',
        array(
        'file_id' => 'pk',
        'file_code' => 'varchar(255) NOT NULL',
        'file_type_id' => 'varchar(255) NOT NULL',
        'file_path' => 'text NOT NULL',
        'file_name' => 'text NOT NULL',
        'file_about' => 'text NOT NULL',
        'file_original_name' => 'text NOT NULL',
        'file_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        )
    );
    App::$cur->db->createTable('files_type',
        array(
        'type_id' => 'pk',
        'type_dir' => 'text NOT NULL',
        'type_ext' => 'varchar(255) NOT NULL',
        'type_allow_resize' => 'tinyint(1) NOT NULL',
        'type_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        )
    );
    App::$cur->db->insert('files_type',
        array(
        'type_dir' => '/static/mediafiles/images/',
        'type_ext' => 'png',
        'type_allow_resize' => '1'
    ));
    App::$cur->db->insert('files_type',
        array(
        'type_dir' => '/static/mediafiles/images/',
        'type_ext' => 'jpeg',
        'type_allow_resize' => '1'
    ));
    App::$cur->db->insert('files_type',
        array(
        'type_dir' => '/static/mediafiles/images/',
        'type_ext' => 'jpg',
        'type_allow_resize' => '1'
    ));
    App::$cur->db->insert('files_type',
        array(
        'type_dir' => '/static/mediafiles/images/',
        'type_ext' => 'gif',
        'type_allow_resize' => '1'
    ));
};
