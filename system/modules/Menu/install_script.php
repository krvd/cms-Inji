<?php
return function ($step = NULL, $params = array()) {

    App::$cur->db->create_table('menu_groups',
        array(
        'mg_id' => 'pk',
        'mg_name' => 'varchar(255) NOT NULL',
        'mg_code' => 'varchar(255) NOT NULL',
        'mg_user_id' => 'int(11) NOT NULL',
        'mg_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        )
    );
    App::$cur->db->create_table('menu_items',
        array(
        'mi_id' => 'pk',
        'mi_name' => 'varchar(255) NOT NULL',
        'mi_href' => 'varchar(255) NOT NULL',
        'mi_type' => 'varchar(255) NOT NULL',
        'mi_advance' => 'text NOT NULL',
        'mi_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'mi_mg_id' => 'int(11) NOT NULL',
        'mi_weight' => 'int(11) NOT NULL',
        )
    );
};
