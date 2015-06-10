<h2>Модели
    <div class ='btn-group pull-right'>
        <a class ='btn btn-sm btn-primary' href ='/admin/modules/createModel/<?= $module; ?>'>Создать</a>
    </div>
</h2>
<?php
$table = new Ui\Table();
$table->name = false;
$table->setCols([
    'Модель',
    'Внешние изменения',
    'Операции'
]);
$path = Module::getModulePath($module) . '/models';
$config = Config::custom(App::$primary->path . '/modules/' . $module . '/generatorHash.php');
if (file_exists($path)) {
    $files = array_slice(scandir($path), 2);
    foreach ($files as $file) {
        if (is_dir($path . '/' . $file)) {
            continue;
        }
        $modelName = pathinfo($file, PATHINFO_FILENAME);
        $table->addRow([
            $modelName,
            (!empty($config['models/' . $file]) && $config['models/' . $file] == md5(file_get_contents($path . '/' . $file))) ? '<b class="text-success">Нету</b>' : '<b class="text-danger">Есть</b>',
            [
                'class'=>'actionTd',
                'html' => '<a class="btn btn-xs btn-success" href="/admin/modules/editModel/' . $module . '/' . $modelName . '"><i class="glyphicon glyphicon-edit"></i></a>'
                . ' <a class="btn btn-xs btn-danger" href="/admin/modules/delModel/' . $module . '/' . $modelName . '"><i class="glyphicon glyphicon-remove"></i></a>'
            ]
        ]);
    }
}
$table->draw();
?>