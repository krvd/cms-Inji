<?php

return function($step = NULL, $params = []) {

    App::$cur->db->createTable('files_file', array(
        'file_id' => 'pk',
        'file_code' => 'varchar(255) NOT NULL',
        'file_type_id' => 'int(11) NOT NULL',
        'file_upload_code' => 'varchar(255) NOT NULL',
        'file_path' => 'text NOT NULL',
        'file_name' => 'varchar(255) NOT NULL',
        'file_about' => 'text NOT NULL',
        'file_original_name' => 'varchar(255) NOT NULL',
        'file_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    App::$cur->db->createTable('files_folder', array(
        'folder_id' => 'pk',
        'folder_dir' => 'varchar(255) NOT NULL',
        'folder_name' => 'varchar(255) NOT NULL',
        'folder_alias' => 'varchar(255) NOT NULL',
        'folder_public' => 'tinyint(1) NOT NULL',
        'folder_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    App::$cur->db->createTable('files_type', array(
        'type_id' => 'pk',
        'type_dir' => 'varchar(255) NOT NULL',
        'type_ext' => 'varchar(255) NOT NULL',
        'type_group' => 'varchar(255) NOT NULL',
        'type_allow_resize' => 'tinyint(1) NOT NULL',
        'type_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    $types = [
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'png',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'jpeg',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'jpg',
            'group' => 'image',
            'allow_resize' => 1,
        ],
        [
            'dir' => '/static/mediafiles/images/',
            'ext' => 'gif',
            'group' => 'image',
            'allow_resize' => 1,
        ],
    ];
    foreach ($types as $type) {
        App::$cur->db->insert('files_type', [
            'type_dir' => $type['dir'],
            'type_ext' => $type['ext'],
            'type_group' => $type['group'],
            'type_allow_resize' => $type['allow_resize']
        ]);
    }
};
