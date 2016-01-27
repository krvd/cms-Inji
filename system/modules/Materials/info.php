<?php

return [
    'name' => 'Материалы сайта',
    'requires' => [
        'Files', 'Widgets'
    ],
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Материалы',
                'href' => '/admin/materials/material',
                'childs' => []
            ]
        ]
    ]
];
