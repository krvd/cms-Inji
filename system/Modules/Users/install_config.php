<?php
return [
    'name' => 'Пользователи',
    'class_name' => 'Users',
    'module_name' => 'Users',
    'needInstall' => true,
    'site_controller_dir' => 'site_module',
    'site_controller_class' => 'users',
    'app_admin_controller_dir' => 'manage',
    'app_admin_controller_class' => 'usersManager',
    'icon' => 'fa fa-users',
    'app_admin_menu' => [
        'index' => [
            'name' => 'Список пользователей',
            'icon' => 'fa fa-list'
        ],
        'create' => [
            'name' => 'Создать пользователя',
            'icon' => 'fa fa-edit'
        ]
    ]
];
