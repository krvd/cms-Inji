<?php
return function ($step = NULL, $params = array()) {

    Inji::app()->db->create_table('roles',
        array(
        'role_id' => 'pk',
        'role_name' => 'varchar(255) NOT NULL',
        'role_group_id' => 'int(11) NOT NULL',
        )
    );
    Inji::app()->db->insert('roles', array(
        'role_name' => 'Гость',
        'role_group_id' => 1
    ));
    Inji::app()->db->insert('roles', array(
        'role_name' => 'Пользователь',
        'role_group_id' => 2
    ));
    Inji::app()->db->insert('roles', array(
        'role_name' => 'Администратор',
        'role_group_id' => 3
    ));
};
