<?php

return [
    'app' => [
        'dostup_tree' => [
        ],
        'denied_redirect' => '/users/login',
        'login_redirect' => '/'
    ],
    'appAdmin' => [
        'dostup_tree' => [
            'Users' => [
                'Users' => [
                    '_access' => [
                        '0' => '3'
                    ],
                    'login' => [
                        '_access' => [
                        ]
                    ]
                ],
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
