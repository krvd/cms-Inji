<?php

return [
    'name' => 'Миграции данных',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Миграции данных',
                'href' => '/admin/migrations',
                'childs' => [
                    [
                        'name' => 'История миграций',
                        'href' => '/admin/migrations/history'
                    ],
                    [
                        'name' => 'Ручная миграция',
                        'href' => '/admin/migrations/manual'
                    ],
                    [
                        'name' => 'Конфигурация',
                        'href' => '/admin/migrations/configure'
                    ]
                ]
            ]
        ]
    ]
];
