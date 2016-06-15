<h1>Настройка доступа к разделам сайта</h1>
<h3>Общие настройки</h3>
<?php
$form = new Ui\Form();
foreach ($defaultConfig as $appType => $access) {
    echo "<h4>Тип приложения: {$appType}</h4>";
    $form->input('radio', "acesstype[{$appType}]", 'Без ограниений доступа', ['value' => 'nolimits', 'checked' => empty($access['_access'])]);
    $form->input('radio', "acesstype[{$appType}]", 'Только для перечисленных групп', ['value' => 'nolimits', 'checked' => !empty($access['_access'])]);
    $form->input('select', "groups[{$appType}]", false, ['values' => \Users\Group::getList(['forSelect' => true]), 'value' => $access['_access'], 'multiple' => true]);
}
foreach ($modules as $module) {
    $controllers = Module::getModuleControllers($module);
}