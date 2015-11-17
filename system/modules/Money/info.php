<?php

return [
    'name' => 'Финансы',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Финансы',
                'href' => '#',
                'childs' => [
                    [
                        'name' => 'Валюты',
                        'href' => '/admin/money/currency'
                    ],
                    [
                        'name' => 'Курсы обмена',
                        'href' => '/admin/money/currency\ExchangeRate'
                    ]
                ]
            ]
        ]
    ]
];
