<?php

return [
    'name' => 'Обмен с 1с',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Обмен с 1с',
                'href' => '/admin/exchange1c',
                'childs' => [
                    [
                        'name' => 'История',
                        'href' => '/admin/exchange1c/exchange'
                    ],
                    [
                        'name' => 'Ручной обмен',
                        'href' => '/admin/exchange1c/manual'
                    ],
                    [
                        'name' => 'Конфигурация',
                        'href' => '/admin/exchange1c/configure'
                    ]
                ]
            ]
        ]
    ]
];
