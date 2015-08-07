<?php

namespace Ecommerce\Item\Offer;

class Price extends \Model {

    static $objectName = 'Цена';
    static $cols = [
        'name' => ['type' => 'text'],
        'price' => ['type' => 'Number'],
        'delivery_weight' => ['type' => 'Number'],
        'article' => ['type' => 'text'],
        'inpack' => ['type' => 'Number'],
        'image_file_id' => ['type' => 'image'],
        'item_price_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'unit_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'unit'],
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
    ];
    static $labels = [
        'name' => 'Название',
        'price' => 'Цена',
        'delivery_weight' => 'Вес',
        'article' => 'Артикул',
        'inpack' => 'В упаковке',
        'image_file_id' => 'Изображение',
        'item_price_type_id' => 'Тип цены',
        'unit_id' => 'Единица измерения',
        'item_id' => 'Товар',
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
            'offer' => [
                'model' => 'Ecommerce\Item\Offer',
                'col' => 'item_offer_id'
            ],
            'unit' => [
                'model' => 'Ecommerce\Unit',
                'col' => 'unit_id'
            ],
            'type' => [
                'model' => 'Ecommerce\Item\Offer\Price\Type',
                'col' => 'item_offer_price_type_id'
            ],
        ];
    }

}
