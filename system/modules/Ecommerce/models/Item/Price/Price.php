<?php

namespace Ecommerce\Item;

class Price extends \Model {

    static $objectName = 'Цена';
    static $cols = [
        'name' => ['type' => 'text'],
        'price' => ['type' => 'numeric'],
        'delivery_weight' => ['type' => 'numeric'],
        'article' => ['type' => 'text'],
        'inpack' => ['type' => 'numeric'],
        'image_file_id' => ['type' => 'image'],
        'item_price_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'unit_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'unit'],
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'warehouses' => ['type' => 'select', 'source' => 'relation', 'relation' => 'warehouses'],
    ];
    static $labels = [
        'name' => 'Название',
        'price' => 'Цена',
        'delivery_weight' => 'Вес',
        'article' => 'Артикль',
        'inpack' => 'В упаковке',
        'image_file_id' => 'Изображение',
        'item_price_type_id' => 'Тип цены',
        'unit_id' => 'Единица измерения',
        'item_id' => 'Товар',
        'warehouses' => 'Склад',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Торговые предлоежния',
            'cols' => [
                'article',
                'name',
                'price',
                'inpack',
                'unit_id',
                'warehouses',
            ]
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                [
                    'name',
                    'price',
                    'delivery_weight',
                ],
                [
                    'article',
                    'inpack',
                    'image_file_id',
                ],
                [
                    'item_price_type_id',
                    'unit_id',
                    'item_id'
                ]
            ]
    ]];

    static function relations() {
        return [
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ],
            'unit' => [
                'model' => 'Ecommerce\Unit',
                'col' => 'unit_id'
            ],
            'type' => [
                'model' => 'Ecommerce\Item\Price\Type',
                'col' => 'item_price_type_id'
            ],
            'warehouses' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Price\Warehouse',
                'col' => 'item_price_id'
            ],
        ];
    }

}
