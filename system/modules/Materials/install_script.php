<?php

return function ($step = NULL, $params = []) {
    $material = new Materials\Material([
        'name' => 'Главная',
        'text' => '<p>Главная страница сайта</p>',
        'default' => '1',
        'preview' => '<p>Главная страница</p>',
        'template' => 'default',
        'viewer' => 'main_page'
    ]);
    $material->save();
};
