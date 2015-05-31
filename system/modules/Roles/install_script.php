<?php
return function ($step = NULL, $params = array()) {

    App::$cur->db->createTable('roles_role',
        array(
        'role_id' => 'pk',
        'role_name' => 'varchar(255) NOT NULL',
        'role_group_id' => 'int(11) NOT NULL',
        )
    );
    App::$cur->db->insert('roles', array(
        'role_name' => 'Гость',
        'role_group_id' => 1
    ));
    App::$cur->db->insert('roles', array(
        'role_name' => 'Пользователь',
        'role_group_id' => 2
    ));
    App::$cur->db->insert('roles', array(
        'role_name' => 'Администратор',
        'role_group_id' => 3
    ));
};
