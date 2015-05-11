<?php

return [
    'storage' => [
        'appAdmin' => [
            'scheme'=>[
                'Menu'=>[
                    'ai'=>2
                ],
                'Item'=>[
                    'ai'=>3
                ]
            ],
            'Menu' => [
                '0' => [
                    'id' => 1,
                    'name' => 'Меню боковой панели',
                    'code'=>'sidebarMenu'
                ]
            ],
            'Item' => [
                [
                    'id' => 1,
                    'type' => 'href',
                    'name' => 'Меню',
                    'href' => '/admin/menu',
                    'Menu_id' => 1
                ], [
                    'id' => 2,
                    'type' => 'href',
                    'name' => 'Темы оформления',
                    'href' => '/admin/view',
                    'Menu_id' => 1
                ],
            ]
        ]
    ]
];
