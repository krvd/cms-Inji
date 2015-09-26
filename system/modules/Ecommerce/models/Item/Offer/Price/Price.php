<?php

namespace Ecommerce\Item\Offer;

class Price extends \Model
{
    static $objectName = 'Цена';
    static $cols = [
        'price' => ['type' => 'Number'],
        'delivery_weight' => ['type' => 'Number'],
        'article' => ['type' => 'text'],
        'inpack' => ['type' => 'Number'],
        'image_file_id' => ['type' => 'image'],
        'item_offer_price_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'unit_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'unit'],
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
    ];
    static $labels = [
        'price' => 'Цена',
        'delivery_weight' => 'Вес',
        'article' => 'Артикул',
        'inpack' => 'В упаковке',
        'image_file_id' => 'Изображение',
        'item_offer_price_type_id' => 'Тип цены',
        'unit_id' => 'Единица измерения',
        'item_offer_id' => 'Товар',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Торговые предлоежния',
            'cols' => [
                'article',
                'item_offer_price_type_id',
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
                    'price',
                    'delivery_weight',
                ],
                [
                    'article',
                    'inpack',
                    'image_file_id',
                ],
                [
                    'item_offer_price_type_id',
                    'unit_id',
                    'item_offer_id'
                ]
            ]
    ]];

    static function relations()
    {
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
