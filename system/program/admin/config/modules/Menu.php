<?php
return [
    'storage' => [
        'appTypeSplit' => '1',
        'appAdmin' => [
            'scheme' => [
                'Menu' => [
                    'ai' => '2'
                ],
                'Item' => [
                    'ai' => '4'
                ]
            ],
            'Menu' => [
                '0' => [
                    'id' => '1',
                    'name' => 'Меню боковой панели',
                    'code' => 'sidebarMenu'
                ]
            ],
            'Item' => [
                '0' => [
                    'id' => '1',
                    'type' => 'href',
                    'name' => 'Меню',
                    'href' => '/admin/menu',
                    'Menu_id' => '1'
                ],
                '1' => [
                    'id' => '2',
                    'type' => 'href',
                    'name' => 'Темы оформления',
                    'href' => '/admin/view',
                    'Menu_id' => '1'
                ],
                '2' => [
                    'Menu_id' => '1',
                    'type' => 'href',
                    'name' => 'Материалы',
                    'href' => '/admin/materials',
                    'id' => '3'
                ]
            ]
        ]
    ]
];