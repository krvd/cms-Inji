<?php

$table = new Ui\Table();
$table->name = 'Модули';
$table->setCols([
    'Модуль',
    'Панель администратора',
    'Публичная часть',
    'Управление'
]);
foreach (App::$primary->config['modules'] as $module) {
    $info = Module::getInfo($module);
    $table->addRow([
        $info['name'],
        '',
        '',
        "<a class = 'btn btn-primary btn-xs' href = '/admin/modules/editor/{$module}'>Редактировать</a>"
    ]);
}

$table->draw();
