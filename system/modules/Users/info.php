<?php

return [
    'name' => 'Пользователи',
    'widgets' => ['Users\loginForm' => 'Форма входа'],
    'requires' => [
        'Files', 'Access'
    ],
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Пользователи',
                'href' => '/admin/users/user',
                'childs' => [
                    [
                        'name' => 'Соц. сети',
                        'href' => '/admin/users/social'
                    ],
                ]
            ]
        ]
    ]
];
