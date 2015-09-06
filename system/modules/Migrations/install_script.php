<?php

return function ($step = NULL, $params = []) {

    App::$cur->db->createTable('migrations_migration', [
        'migration_id' => 'pk',
        'migration_name' => 'varchar(255) NOT NULL',
        'migration_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('migrations_migration_map', [
        'migration_map_id' => 'pk',
        'migration_map_name' => 'varchar(255) NOT NULL',
        'migration_map_migration_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_map_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('migrations_migration_map_path', [
        'migration_map_path_id' => 'pk',
        'migration_map_path_migration_map_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_map_path_parent_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_map_path_object_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_map_path_path' => 'text NOT NULL',
        'migration_map_path_item' => 'varchar(255) NOT NULL',
        'migration_map_path_type' => 'varchar(255) NOT NULL',
    ]);
    App::$cur->db->createTable('migrations_migration_object', [
        'migration_object_id' => 'pk',
        'migration_object_migration_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_object_name' => 'varchar(255) NOT NULL',
        'migration_object_code' => 'varchar(255) NOT NULL',
        'migration_object_model' => 'varchar(255) NOT NULL',
        'migration_object_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('migrations_migration_object_param', [
        'migration_object_param_id' => 'pk',
        'migration_object_param_parent_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_object_param_object_id' => 'int(11) UNSIGNED NOT NULL',
        'migration_object_param_code' => 'varchar(255) NOT NULL',
        'migration_object_param_type' => 'varchar(255) NOT NULL',
        'migration_object_param_value' => 'varchar(255) NOT NULL',
        'migration_object_param_options' => 'text NOT NULL',
        'migration_object_param_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('migrations_migration_object_param_value', [
        'migration_object_param_value_id' => 'pk',
        'migration_object_param_value_original' => 'text NOT NULL',
        'migration_object_param_value_replace' => 'text NOT NULL',
        'migration_object_param_value_param_id' => 'int(11) UNSIGNED NOT NULL',
    ]);
    App::$cur->db->createTable('migrations_log', [
        'log_id' => 'pk',
        'log_migration_id' => 'int(11) UNSIGNED NOT NULL',
        'log_migration_map_id' => 'int(11) UNSIGNED NOT NULL',
        'log_result' => 'varchar(255) NOT NULL',
        'log_source' => 'text NOT NULL',
        'log_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'log_date_end' => 'timestamp NOT NULL DEFAULT 0',
    ]);
    App::$cur->db->createTable('migrations_log_event', [
        'log_event_id' => 'pk',
        'log_event_log_id' => 'int(11) UNSIGNED NOT NULL',
        'log_event_map_path_id' => 'int(11) UNSIGNED NOT NULL',
        'log_event_type' => 'varchar(255) NOT NULL',
        'log_event_info' => 'text NOT NULL',
        'log_event_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
    App::$cur->db->createTable('migrations_id', [
        'id_id' => 'pk',
        'id_object_id' => 'int(11) UNSIGNED NOT NULL',
        'id_type' => 'varchar(255) NOT NULL',
        'id_parse_id' => 'varchar(255) NOT NULL',
    ]);
};
