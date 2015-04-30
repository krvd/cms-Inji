<?php
return function($step = NULL, $params = array()) {

    Inji::app()->db->create_table('files',
        array(
        'file_id' => 'pk',
        'file_code' => 'varchar(255) NOT NULL',
        'file_type' => 'varchar(255) NOT NULL',
        'file_path' => 'text NOT NULL',
        'file_name' => 'text NOT NULL',
        'file_about' => 'text NOT NULL',
        'file_original_name' => 'text NOT NULL',
        'file_user_id' => 'int(11) NOT NULL',
        'file_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        )
    );
    Inji::app()->db->create_table('file_types',
        array(
        'file_type_id' => 'pk',
        'file_type_dir' => 'text NOT NULL',
        'file_type_ext' => 'varchar(255) NOT NULL',
        'file_type_allow_resize' => 'tinyint(1) NOT NULL',
        'file_type_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'file_type_user_id_create' => 'int(11) NOT NULL',
        )
    );
    Inji::app()->db->insert('file_types',
        array(
        'file_type_dir' => '/static/mediafiles/images/',
        'file_type_ext' => 'png',
        'file_type_allow_resize' => '1'
    ));
    Inji::app()->db->insert('file_types',
        array(
        'file_type_dir' => '/static/mediafiles/images/',
        'file_type_ext' => 'jpeg',
        'file_type_allow_resize' => '1'
    ));
    Inji::app()->db->insert('file_types',
        array(
        'file_type_dir' => '/static/mediafiles/images/',
        'file_type_ext' => 'jpg',
        'file_type_allow_resize' => '1'
    ));
    Inji::app()->db->insert('file_types',
        array(
        'file_type_dir' => '/static/mediafiles/images/',
        'file_type_ext' => 'gif',
        'file_type_allow_resize' => '1'
    ));
};
