<h1>Ручная миграция данных</h1>
<?php
$form = new Ui\Form();
$form->begin();
$form->input('select', 'map', 'Выберите карту миграции', ['values' => \Migrations\Migration\Map::getList(['forSelect' => true])]);
$form->input('file', 'file', 'Выберите файл');
$form->end('Начать');
?>