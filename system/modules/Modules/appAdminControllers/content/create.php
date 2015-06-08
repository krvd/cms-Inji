<?php

$form = new Ui\Form();
$form->begin('Создание модуля');
$form->input('text', 'name', 'Название модуля', ['placeholder' => 'Например: Статьи']);
$form->input('text', 'codeName', 'Кодовое обозначение', ['placeholder' => 'Например: Articles', 'helpText' => 'Используйте имена на английском языке. Это обозначение используется для обращения к модулю из скрипта']);
$form->end('Создать');
