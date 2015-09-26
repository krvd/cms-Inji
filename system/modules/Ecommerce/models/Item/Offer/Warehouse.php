<?php

namespace Ecommerce\Item\Offer;

class Warehouse extends \Model
{
    static $objectName = 'Товар на складе';
    static $labels = [
        'count' => 'Количество',
        'warehouse_id' => 'Склад',
        'item_offer_id' => 'Торговое предложение',
    ];
    static $cols = [
        'count' => ['type' => 'text'],
        'warehouse_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'warehouse'],
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Складской учет',
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

    static function relations()
    {
        return [
            'warehouse' => [
                'model' => 'Ecommerce\Warehouse',
                'col' => 'warehouse_id'
            ],
            'offer' => [
                'model' => 'Ecommerce\Item\Offer',
                'col' => 'item_offer_id'
            ],
        ];
    }

}
