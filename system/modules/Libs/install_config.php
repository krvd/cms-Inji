<?php

return array(
    'name' => 'Менеджер подключаемых библиотек',
    'class_name' => 'LibsAsseter',
    'module_name' => 'LibsAsseter',
    'needInstall' => true,
    'autoload' => true,
    'site_controller_dir' => 'site_module',
    'site_controller_class' => 'LibsAsseter',
    'app_admin_controller_dir' => 'manage',
    'app_admin_controller_class' => 'LibsAsseterManager',
    'icon' => 'fa fa-book',
    'app_admin_menu' => []
);
