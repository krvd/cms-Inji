<?php
return function ($step = NULL, $params = array()) {

    App::$cur->db->create_table('groups', array(
        'group_id' => 'pk',
        'group_name' => 'varchar(255) NOT NULL',
        )
    );
    App::$cur->db->insert('groups', array(
        'group_name' => 'Гости'
    ));
    App::$cur->db->insert('groups', array(
        'group_name' => 'Зарегистрированые'
    ));
    App::$cur->db->insert('groups', array(
        'group_name' => 'Администрация'
    ));
};
