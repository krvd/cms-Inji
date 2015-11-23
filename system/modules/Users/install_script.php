<?php

return function ($step = NULL, $params = array()) {
    //users 
    App::$cur->db->createTable('users_user', array(
        'user_id' => 'pk',
        'user_login' => 'varchar(255) NOT NULL',
        'user_mail' => 'varchar(255) NOT NULL',
        'user_pass' => 'text NOT NULL',
        'user_parent_id' => 'INT(11) UNSIGNED NOT NULL',
        'user_group_id' => 'INT(11) UNSIGNED NOT NULL',
        'user_role_id' => 'INT(11) UNSIGNED NOT NULL',
        'user_activ' => 'text NOT NULL',
        'user_admin_text' => 'text NOT NULL',
        'user_activation' => 'varchar(255) NOT NULL',
        'user_reg_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
        'user_last_activ' => 'timestamp',
    ));
    if (!empty($params['user'])) {
        $userId = App::$cur->db->insert('users_user', array(
            'user_login' => $params['user']['user_login'],
            'user_mail' => $params['user']['user_mail'],
            'user_pass' => password_hash($params['user']['user_pass'], PASSWORD_DEFAULT),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    } else {
        $userId = App::$cur->db->insert('users_user', array(
            'user_login' => 'admin',
            'user_mail' => 'admin@' . idn_to_utf8(INJI_DOMAIN_NAME),
            'user_pass' => password_hash('admin', PASSWORD_DEFAULT),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    }
    //users socials
    App::$cur->db->createTable('users_social', array(
        'social_id' => 'pk',
        'social_name' => 'varchar(255) NOT NULL',
        'social_code' => 'varchar(255) NOT NULL',
        'social_active' => 'tinyint(1) UNSIGNED NOT NULL',
        'social_object_name' => 'varchar(255) NOT NULL',
        'social_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users socials config
    App::$cur->db->createTable('users_social_config', array(
        'social_config_id' => 'pk',
        'social_config_name' => 'varchar(255) NOT NULL',
        'social_config_value' => 'varchar(255) NOT NULL',
        'social_config_social_id' => 'int(11) UNSIGNED NOT NULL',
        'social_config_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    $socials = [
        [
            'social_name' => 'Вконтакте',
            'social_code' => 'vk',
            'social_object_name' => 'Vk'
        ],
    ];
    $socialsConfig = [
        [
            [
                'social_config_name' => 'appId'
            ],
            [
                'social_config_name' => 'secret'
            ]
        ]
    ];
    foreach ($socials as $key => $social) {
        $id = App::$cur->db->insert('users_social', $social);
        foreach ($socialsConfig[$key] as $config) {
            App::$cur->db->insert('users_social_config', $config);
        }
    }
    //users links social
    App::$cur->db->createTable('users_user_social', [
        'user_social_id' => 'pk',
        'user_social_social_id' => 'int(11) UNSIGNED NOT NULL',
        'user_social_uid' => 'varchar(255) NOT NULL',
        'user_social_user_id' => 'int(11) UNSIGNED NOT NULL',
        'user_social_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
    //users session
    App::$cur->db->createTable('users_session', array(
        'session_id' => 'pk',
        'session_hash' => 'varchar(255) NOT NULL',
        'session_ip' => 'varchar(255) NOT NULL',
        'session_agent' => 'varchar(255) NOT NULL',
        'session_user_id' => 'int(11) UNSIGNED NOT NULL',
        'session_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users passre
    App::$cur->db->createTable('users_passre', array(
        'passre_id' => 'pk',
        'passre_hash' => 'text NOT NULL',
        'passre_user_id' => 'int(11) UNSIGNED NOT NULL',
        'passre_status' => 'int(1) UNSIGNED NOT NULL',
        'passre_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users info
    App::$cur->db->createTable('users_info', array(
        'info_id' => 'pk',
        'info_first_name' => 'varchar(255) NOT NULL',
        'info_last_name' => 'varchar(255) NOT NULL',
        'info_middle_name' => 'varchar(255) NOT NULL',
        'info_phone' => 'varchar(255) NOT NULL',
        'info_country' => 'varchar(255) NOT NULL',
        'info_city' => 'varchar(255) NOT NULL',
        'info_user_id' => 'int(11) UNSIGNED NOT NULL',
        'info_sex' => 'int(1) UNSIGNED NOT NULL',
        'info_photo_file_id' => 'int(11) UNSIGNED NOT NULL',
        'info_bday' => 'date NOT NULL',
    ));
    App::$cur->db->insert('users_info', array(
        'info_first_name' => 'Администратор',
        'info_user_id' => $userId,
    ));
    //users invite
    App::$cur->db->createTable('users_user_invite', array(
        'user_invite_id' => 'pk',
        'user_invite_code' => 'varchar(255) NOT NULL',
        'user_invite_type' => 'varchar(255) NOT NULL',
        'user_invite_limit' => 'int(11) UNSIGNED NOT NULL',
        'user_invite_count' => 'int(11) UNSIGNED NOT NULL',
        'user_invite_user_id' => 'int(11) UNSIGNED NOT NULL',
        'user_invite_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    //users invite history
    App::$cur->db->createTable('users_user_invite_history', array(
        'user_invite_history_id' => 'pk',
        'user_invite_history_type' => 'varchar(255) NOT NULL',
        'user_invite_history_user_invite_id' => 'int(11) UNSIGNED NOT NULL',
        'user_invite_history_user_id' => 'int(11) UNSIGNED NOT NULL',
        'user_invite_history_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
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
