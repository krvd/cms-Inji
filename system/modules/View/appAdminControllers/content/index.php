<?php

$table = new Ui\Table();
$table->name = 'Шаблоны';
$table->addButton([
    'text' => 'Создать шаблон',
    'href' => '/admin/view/createTemplate',
]);
$table->setCols([
    'Шаблон',
    '',
    ''
]);
if (!empty($templates['app']['installed']))
    foreach ($templates['app']['installed'] as $template => $name) {
        $table->addRow([
            $name,
            '<a href = "/admin/view/setDefault/' . $template . '">Установить по умолчанию</a>',
            '<a href = "/admin/view/editTemplate/' . $template . '">Редактировать</a>'
        ]);
    }
$table->draw();
?>