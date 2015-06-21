<?php

return [
    'access' => [
        'accessTree' => [
            'app' => [
                '_access' => [
                    2, 3
                ],
                'Users' => [
                    'login' => [
                        '_access' => []
                    ],
                    'registration' => [
                        '_access' => []
                    ]
                ],
            ],
            'appAdmin' => [
                'Users' => [
                    'login' => [
                        '_access' => []
                    ]
                ],
            ]
        ]
    ],
    'loginUrl' => [
        'app' => '/',
        'appAdmin' => '/admin'
    ]
];
