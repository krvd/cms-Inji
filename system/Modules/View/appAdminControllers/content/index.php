<?php

$table = new Ui\Table();
$table->setCols([
    'Шаблон',
    'Действия'
]);
$table->addButton([
    'onClick' => 'forms.show("View/Template")',
]);
$table->draw();
?>