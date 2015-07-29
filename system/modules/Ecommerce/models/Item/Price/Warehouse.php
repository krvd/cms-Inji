<?php

namespace Ecommerce\Item\Price;

class Warehouse extends \Model {

    static $objectName = 'Товар на складе';
    static $labels = [
        'count' => 'Количество',
        'warehouse_id' => 'Склад',
        'item_price_id' => 'Торговое предложение',
    ];
    static $cols = [
        'count' => ['type' => 'text'],
        'warehouse_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'warehouse'],
        'item_price_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'price'],
    ];
    static $dataManagers = [
        'manager' => [
            'name'=>'Складской учет',
            'cols' => [
                'warehouse_id',
                'count',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['item_price_id', 'warehouse_id'],
                ['count'],
            ]
    ]];

    static function relations() {
        return [
            'warehouse' => [
                'model' => 'Ecommerce\Warehouse',
                'col' => 'warehouse_id'
            ],
            'price' => [
                'model' => 'Ecommerce\Item\Price',
                'col' => 'item_price_id'
            ],
        ];
    }

}
