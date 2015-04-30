<?php

return [
    'site' => [
        'dostup_tree' => [
        ],
        'denied_redirect' => '/users/login',
        'login_redirect' => '/'
    ],
    'app_admin' => [
        'dostup_tree' => [
            'usersManager' => [
                '_access' => [
                    '0' => '3'
                ],
                'login' => [
                    '_access' => [
                    ]
                ]
            ],
            'Install' => [
                '_access' => [
                ]
            ],
            '_access' => [
                '0' => '3'
            ]
        ],
        'denied_redirect' => '/admin/users/login',
        'login_redirect' => '/admin/'
    ],
];
