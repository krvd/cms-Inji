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
                        'name' => 'Платежные системы',
                        'href' => '/admin/money/merchant'
                    ],
                    [
                        'name' => 'Валюты',
                        'href' => '/admin/money/currency'
                    ],
                    [
                        'name' => 'Курсы обмена',
                        'href' => '/admin/money/currency%5CExchangeRate'
                    ]
                ]
            ]
        ]
    ]
];
