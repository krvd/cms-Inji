<?php

namespace Ecommerce;

class Delivery extends \Model {

    static $objectName = 'Доставка';
    static $cols = [
        'name' => ['type' => 'text'],
        'price' => ['type' => 'text'],
        'max_cart_price' => ['type' => 'text'],
        'icon_file_id' => ['type' => 'image'],
    ];
    static $labels = [
        'name' => 'Название',
        'price' => 'Стоимость',
        'max_cart_price' => 'Басплатно при',
        'icon_file_id' => 'Иконка',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Варианты доставки',
            'cols' => [
                'name',
                'price',
                'max_cart_price',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'price'],
                ['max_cart_price', 'icon_file_id'],
            ]
    ]];

    static function relations() {
        return [
            'icon' => [
                'model' => 'Files\File',
                'col' => 'icon_file_id'
            ],
        ];
    }

}
