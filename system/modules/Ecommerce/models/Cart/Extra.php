<?php

namespace Ecommerce\Cart;

class Extra extends \Model {

    static $labels = [
        'name' => 'Название',
        'price' => 'Цена',
        'count' => 'Количество',
        'cart_id' => 'Корзина'
    ];
    static $cols = [
        'cart_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cart'],
        'name' => ['type' => 'text'],
        'price' => ['type' => 'text'],
        'count' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name'=>'Дополнительно',
            'cols' => [
                'name',
                'price',
                'count',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'price'],
                ['count', 'cart_id'],
            ]
        ]
    ];

    function afterSave() {
        $this->cart->calc();
    }

    static function relations() {
        return [
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ],
            'price' => [
                'model' => 'Ecommerce\Item\Offer\Price',
                'col' => 'item_offer_price_id'
            ],
            'cart' => [
                'model' => 'Ecommerce\Cart',
                'col' => 'cart_id'
            ]
        ];
    }

}
