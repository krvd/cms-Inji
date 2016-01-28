<?php

return function ($step = NULL, $params = array()) {
    $material = new Materials\Material([
        'name' => 'Главная',
        'text' => '<p>Главная страница сайта</p>',
        'default' => '1',
        'preview' => '<p>Главная страница</p>',
    ]);
    $material->save();
};
