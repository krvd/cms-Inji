<?php

namespace Ecommerce\Cart;

class Info extends \Model {

    static $labels = [
        'name' => 'название',
        'value' => 'Значение',
        'useradds_field_id' => 'Поле',
        'cart_id' => 'Корзина'
    ];
    static $cols = [
        'useradds_field_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'field'],
        'cart_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cart'],
        'name' => ['type' => 'text'],
        'value' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name'=>'Дополнительно',
            'cols' => [
                'name',
                'value',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'value'],
                ['useradds_field_id', 'cart_id'],
            ]
        ]
    ];

    function afterSave() {
        $this->cart->calc();
    }

    static function relations() {
        return [
            'cart' => [
                'model' => 'Ecommerce\Cart',
                'col' => 'cart_id'
            ],
            'field' => [
                'model' => 'Ecommerce\UserAdds\Field',
                'col' => 'useradds_field_id'
            ]
        ];
    }

}
