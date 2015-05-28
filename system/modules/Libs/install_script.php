<?php

return function ($step = NULL, $params = array()) {
    App::$cur->db->createTable('libs_asseter_front_libs', array(
        'lafl_id' => 'pk',
        'lafl_name' => 'varchar(255) NOT NULL',
        'lafl_version' => 'varchar(255) NOT NULL',
        'lafl_required' => 'text NOT NULL',
        'lafl_enabled' => 'BOOL NOT NULL',
    ));
    App::$cur->db->createTable('libs_asseter_front_lib_files', array(
        'laflf_id' => 'pk',
        'laflf_lafl_id' => 'int(11) NOT NULL',
        'laflf_file' => 'varchar(255) NOT NULL',
        'laflf_type' => 'text NOT NULL',
    ));
    //jQuery
    $jQueryId = App::$cur->db->insert('libs_asseter_front_libs', array(
        'lafl_name' => 'jQuery',
        'lafl_version' => '1.11.2',
        'lafl_required' => '',
        'lafl_enabled' => '1',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $jQueryId,
        'laflf_file' => 'libs/jquery/jquery-1.11.2.min.js',
        'laflf_type' => 'js',
    ));
    //Bootstrap
    $bootstrapId = App::$cur->db->insert('libs_asseter_front_libs', array(
        'lafl_name' => 'Bootstrap',
        'lafl_version' => '3.3.4',
        'lafl_required' => $jQueryId,
        'lafl_enabled' => '1',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $bootstrapId,
        'laflf_file' => 'libs/bootstrap/css/bootstrap.min.css',
        'laflf_type' => 'css',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $bootstrapId,
        'laflf_file' => 'libs/bootstrap/js/bootstrap.min.js',
        'laflf_type' => 'js',
    ));
    //Font-awesome
    $fontAwesomeId = App::$cur->db->insert('libs_asseter_front_libs', array(
        'lafl_name' => 'Font-awesome',
        'lafl_version' => '4.3.0',
        'lafl_required' => '',
        'lafl_enabled' => '1',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $fontAwesomeId,
        'laflf_file' => 'libs/font-awesome/css/font-awesome.min.css',
        'laflf_type' => 'css',
    ));
    //bxslider
    $id = App::$cur->db->insert('libs_asseter_front_libs', array(
        'lafl_name' => 'bxslider',
        'lafl_version' => '4.2.3',
        'lafl_required' => $jQueryId,
        'lafl_enabled' => '0',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $id,
        'laflf_file' => 'libs/bxslider/jquery.bxslider.min.js',
        'laflf_type' => 'js',
    ));
    App::$cur->db->insert('libs_asseter_front_lib_files', array(
        'laflf_lafl_id' => $id,
        'laflf_file' => 'libs/bxslider/jquery.bxslider.css',
        'laflf_type' => 'css',
    ));
};
