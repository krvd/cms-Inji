<?php

$table = new Ui\Table();
$table->name = 'Установленные модули';
$table->addButton(['href' => '/admin/modules/create', 'text' => 'Создать']);
$table->addButton(['href' => '/admin/modules/install', 'text' => 'Установить']);
$table->setCols([
    'Модуль',
    'Панель администратора',
    'Публичная часть',
    'Управление',
    'По умолчанию'
]);
$default = !empty(App::$primary->config['defaultModule']) ? App::$primary->config['defaultModule'] : '';

foreach (Module::getInstalled(App::$cur) as $module) {
    $info = Module::getInfo($module);
    $table->addRow([
        empty($info['name']) ? $module : $info['name'],
        '',
        '',
        "<a class = 'btn btn-primary btn-xs' href = '/admin/modules/editor/{$module}'>Редактировать</a>",
        $default == $module ? 'По умолчанию' : "<a class = 'btn btn-primary btn-xs' href = '/admin/modules/setDefault/{$module}'>Установить по умолчанию</a>"
    ]);
}


$table->draw();
