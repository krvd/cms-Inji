<?php

return [
    'access' => [
        'accessTree' => [
            'app' => [
                '_access' => [
                ],
                'deniedUrl' => '/users/login',
                'msg' => 'Для доступа к этому разделу сайта необходимо авторизоваться'
            ],
            'appAdmin' => [
                '_access' => [
                    '0' => '3'
                ],
                'deniedUrl' => '/admin/users/login',
                'msg' => 'Для доступа к этому разделу сайта необходимо авторизоваться'
            ],
        ],
    ]
];
