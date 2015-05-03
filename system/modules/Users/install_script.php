<?php

return function ($step = NULL, $params = array()) {
    Inji::app()->db->create_table('users', array(
        'user_id' => 'pk',
        'user_login' => 'varchar(255) NOT NULL',
        'user_name' => 'varchar(255) NOT NULL',
        'user_mail' => 'varchar(255) NOT NULL',
        'user_phone' => 'varchar(255) NOT NULL',
        'user_vk_href' => 'varchar(255) NOT NULL',
        'user_ok_href' => 'varchar(255) NOT NULL',
        'user_pass' => 'text NOT NULL',
        'user_parent_id' => 'INT NOT NULL',
        'user_photo' => 'INT NOT NULL',
        'user_group_id' => 'INT NOT NULL',
        'user_role_id' => 'INT NOT NULL',
        'user_activ' => 'text NOT NULL',
        'user_city' => 'text NOT NULL',
        'user_about' => 'text NOT NULL',
        'user_birthday' => 'DATE NOT NULL',
        'user_admin_text' => 'text NOT NULL',
        'user_last_activ' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    ));
    if (!empty($params['user'])) {
        Inji::app()->db->insert('users', array(
            'user_login' => $params['user']['user_login'],
            'user_name' => 'Администратор',
            'user_mail' => $params['user']['user_mail'],
            'user_pass' => Inji::app()->Users->hashpass($params['user']['user_pass']),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    } else {
        Inji::app()->db->insert('users', array(
            'user_login' => 'admin',
            'user_name' => 'Администратор',
            'user_mail' => 'admin@' . INJI_DOMAIN_NAME,
            'user_pass' => Inji::app()->Users->hashpass('admin'),
            'user_group_id' => '3',
            'user_role_id' => '3',
        ));
    }
    Inji::app()->db->create_table('user_passre', array(
        'up_id' => 'pk',
        'up_hash' => 'text NOT NULL',
        'up_user_id' => 'int(11) NOT NULL',
        'up_status' => 'int(11) NOT NULL',
        'up_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            )
    );
    Inji::app()->db->create_table('user_invites', array(
        'ui_id' => 'pk',
        'ui_mail' => 'VARCHAR(255) NOT NULL',
        'ui_status' => 'TINYINT(1) NOT NULL',
        'ui_code' => 'VARCHAR(255) NOT NULL',
        'ui_user_id' => 'int(11) NOT NULL',
        'ui_date' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            )
    );
};
