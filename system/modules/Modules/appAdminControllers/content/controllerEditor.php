<?php

$table = new Ui\Table();
$table->name = 'Страницы';
$table->setCols([
    'Адрес',
    'Операции'
]);
$table->addButton([
    'href' => "/admin/modules/createControllerMethod/{$module}/{$type}/{$controller}",
    'text' => 'Создать'
]);
$class = CodeGenerator::parseClass(Modules::getModulePath($module) . '/' . $type . '/' . $controller . '.php');
foreach ($class->methods as $method) {
    $name = str_replace('Action', '', $method->name);
    $table->addRow([
        $name,
        [
            'class' => 'actionTd',
            'html' => '<a class="btn btn-xs btn-success" href="/admin/modules/editControllerMethod/' . $module . '/' . $type . '/' . $controller . '/' . $name . '"><i class="glyphicon glyphicon-edit"></i></a>'
            . ' <a class="btn btn-xs btn-danger" href="/admin/modules/delControllerMethod/' . $module . '/' . $type . '/' . $controller . '/' . $name . '"><i class="glyphicon glyphicon-remove"></i></a>'
        ]
    ]);
}
$table->draw();
