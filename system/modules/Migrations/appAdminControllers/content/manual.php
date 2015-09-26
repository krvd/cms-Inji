<h1>Ручная миграция данных</h1>
<?php
$form = new Ui\Form();
$form->begin();
$form->input('select', 'migration', 'Выберите миграцию', ['values' => $selectArray]);
$form->end('Начать');
?>