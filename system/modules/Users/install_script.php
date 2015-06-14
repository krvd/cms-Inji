<?php

return function ($step = NULL, $params = array()) {
    //users 
    App::$cur->db->createTable('users_user', array(
        'user_id' => 'pk',
        'user_login' => 'varchar(255) NOT NULL',
        'user_mail' => 'varchar(255) NOT NULL',
        'user_pass' => 'text NOT NULL',
        'user_parent_id' => 'INT NOT NULL',
        'user_group_id' => 'INT NOT NULL',
        'user_role_id' => 'INT NOT NULL',
        'user_activ' => 'text NOT NULL',
        'user_admin_text' => 'text NOT NULL',
        'user_reg_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
        'user_last_activ' => 'timestamp',
    ));
    if (!empty($params['user'])) {
        App::$cur->db->insert('users_user', array(
            'user_login' => $params['user']['user_login'],
            'user_mail' => $params['user']['user_mail'],
            'user_pass' => App::$cur->Users->hashpass($params['user']['user_pass']),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    } else {
        App::$cur->db->insert('users_user', array(
            'user_login' => 'admin',
            'user_mail' => 'admin@' . INJI_DOMAIN_NAME,
            'user_pass' => App::$cur->Users->hashpass('admin'),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    }
    //users session
    App::$cur->db->createTable('users_session', array(
        'session_id' => 'pk',
        'session_hash' => 'varchar(255) NOT NULL',
        'session_ip' => 'varchar(255) NOT NULL',
        'session_agent' => 'varchar(255) NOT NULL',
        'session_user_id' => 'int(11) NOT NULL',
        'session_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users passre
    App::$cur->db->createTable('users_passre', array(
        'passre_id' => 'pk',
        'passre_hash' => 'text NOT NULL',
        'passre_user_id' => 'int(11) NOT NULL',
        'passre_status' => 'int(11) NOT NULL',
        'passre_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users info
    App::$cur->db->createTable('users_info', array(
        'info_id' => 'pk',
        'info_first_name' => 'varchar(255) NOT NULL',
        'info_last_name' => 'varchar(255) NOT NULL',
        'info_middle_name' => 'varchar(255) NOT NULL',
        'info_phone' => 'varchar(255) NOT NULL',
        'info_city' => 'varchar(255) NOT NULL',
        'info_user_id' => 'int(11) NOT NULL',
        'info_sex' => 'int(11) NOT NULL',
        'info_photo_file_id' => 'int(11) NOT NULL',
        'info_bday' => 'datetime NOT NULL',
    ));
    //users group
    App::$cur->db->createTable('users_group', array(
        'group_id' => 'pk',
        'group_name' => 'varchar(255) NOT NULL',
    ));
    App::$cur->db->insert('users_group', array(
        'group_name' => 'Гости'
    ));
    App::$cur->db->insert('users_group', array(
        'group_name' => 'Зарегистрированые'
    ));
    App::$cur->db->insert('users_group', array(
        'group_name' => 'Администрация'
    ));
    //users roles
    App::$cur->db->createTable('users_role', array(
        'role_id' => 'pk',
        'role_name' => 'varchar(255) NOT NULL',
        'role_group_id' => 'int(11) NOT NULL',
    ));
    App::$cur->db->insert('users_role', array(
        'role_name' => 'Гость',
        'role_group_id' => 1
    ));
    App::$cur->db->insert('users_role', array(
        'role_name' => 'Пользователь',
        'role_group_id' => 2
    ));
    App::$cur->db->insert('users_role', array(
        'role_name' => 'Администратор',
        'role_group_id' => 3
    ));
};
