<?php

return array(
    'name' => 'Онлайн-магазин',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Интернет-магазин',
                'href' => '/admin/ecommerce/dashboard',
                'childs' => [
                    [
                        'name' => 'Список Товаров',
                        'href' => '/admin/ecommerce/Item'
                    ],
                    [
                        'name' => 'Заказы',
                        'href' => '/admin/ecommerce/Cart'
                    ],
                    [
                        'name' => 'Конфигурация',
                        'href' => '/admin/ecommerce/configure'
                    ]
                ]
            ]
        ]
    ]
);
