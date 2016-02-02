<?php

return function ($step = NULL, $params = []) {

    \App::$cur->db->createTable('userforms_form', array(
        'form_id' => 'pk',
        'form_user_id' => 'INT(11) NOT NULL',
        'form_name' => 'varchar(255) NOT NULL',
        'form_description' => 'text NOT NULL',
        'form_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    \App::$cur->db->createTable('userforms_input', array(
        'input_id' => 'pk',
        'input_form_id' => 'INT(11) NOT NULL',
        'input_user_id' => 'INT(11) NOT NULL',
        'input_label' => 'varchar(255) NOT NULL',
        'input_type' => 'varchar(255) NOT NULL',
        'input_required' => 'tinyint(1) NOT NULL',
        'input_params' => 'text NOT NULL',
        'input_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
    \App::$cur->db->createTable('userforms_recive', array(
        'recive_id' => 'pk',
        'recive_form_id' => 'INT(11) NOT NULL',
        'recive_user_id' => 'INT(11) NOT NULL',
        'recive_data' => 'text NOT NULL',
        'recive_read' => 'tinyint(1) NOT NULL',
        'recive_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ));
};
