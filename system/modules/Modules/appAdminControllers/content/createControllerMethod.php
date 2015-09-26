<?php

$form = new Ui\Form();
$form->begin('Создание страницы');
$form->input('text', 'url', 'Адрес', ['placeholder' => 'Например: index']);
$form->end('Создать');
