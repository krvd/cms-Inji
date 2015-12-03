<?php

namespace Ecommerce\Cart;

class Extra extends \Model
{
    static $labels = [
        'name' => 'Название',
        'price' => 'Цена',
        'count' => 'Количество',
        'cart_id' => 'Корзина',
        'currency_id' => 'Валюта',
    ];
    static $cols = [
        'cart_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cart'],
        'name' => ['type' => 'text'],
        'price' => ['type' => 'decimal'],
        'count' => ['type' => 'decimal'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency']
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Дополнительно',
            'cols' => [
                'name',
                'price',
                'currency_id',
                'count',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['price', 'currency_id'],
                ['count', 'cart_id'],
            ]
        ]
    ];

    function afterSave()
    {
        $this->cart->calc();
    }

    static function relations()
    {
        return [
            'cart' => [
                'model' => 'Ecommerce\Cart',
                'col' => 'cart_id'
            ],
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ]
        ];
    }

}
