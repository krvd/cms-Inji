<?php

return [
    'name' => 'Миграции данных',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Миграции данных',
                'href' => '/admin/migrations/migration',
                'childs' => [
                    [
                        'name' => 'История миграций',
                        'href' => '/admin/migrations/log'
                    ],
                    [
                        'name' => 'Ручная миграция',
                        'href' => '/admin/migrations/manual'
                    ]
                ]
            ]
        ]
    ]
];
