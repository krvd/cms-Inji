<?php

$form = new Ui\Form();
$form->begin('Создание контроллера');
$form->input('select', 'type', 'Тип контроллера', ['values' => ['appControllers'=>'Для сайта','appAdminControllers'=>'Для админ панели','Controllers'=>'Общий']]);
$form->end('Создать');
