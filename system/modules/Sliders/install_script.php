<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('sliders_slider', [
        'slider_id' => 'pk',
        'slider_name' => 'varchar(255) NOT NULL',
        'slider_description' => 'text NOT NULL',
        'slider_user_id' => 'int(11) NOT NULL',
        'slider_image_file_id' => 'int(11) NOT NULL',
        'slider_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('sliders_slide', [
        'slide_id' => 'pk',
        'slide_slider_id' => 'INT(11) NOT NULL',
        'slide_user_id' => 'INT(11) NOT NULL',
        'slide_name' => 'varchar(255) NOT NULL',
        'slide_description' => 'text NOT NULL',
        'slide_weight' => 'INT(11) NOT NULL',
        'slide_image_file_id' => 'INT(11) NOT NULL',
        'slide_date_create' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
    ]);
};
