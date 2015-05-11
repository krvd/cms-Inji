<?php
return array(
    'name' => 'Материалы сайта',
    'class_name' => 'Materials',
    'module_name' => 'Materials',
    'site_controller_dir' => 'site_module',
    'site_controller_class' => 'materials',
    'app_admin_controller_dir' => 'manage',
    'app_admin_controller_class' => 'materialsManager',
    'icon' => 'fa fa-file-text-o',
    'app_admin_menu' => [
        'index' => [
            'name' => 'Список материалов',
            'icon' => 'fa fa-list'
        ],
        'create' => [
            'name' => 'Создать материал',
            'icon' => 'fa fa-edit'
        ]
    ]
);
