<?php
return function ($step = NULL, $params = array()) {

    Inji::app()->db->create_table('groups', array(
        'group_id' => 'pk',
        'group_name' => 'varchar(255) NOT NULL',
        )
    );
    Inji::app()->db->insert('groups', array(
        'group_name' => 'Гости'
    ));
    Inji::app()->db->insert('groups', array(
        'group_name' => 'Зарегистрированые'
    ));
    Inji::app()->db->insert('groups', array(
        'group_name' => 'Администрация'
    ));
};
