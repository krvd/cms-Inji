<?php

namespace Ecommerce\Item\Offer;

class Price extends \Model
{
    static $objectName = 'Цена';
    static $cols = [
        'price' => ['type' => 'text'],
        'item_offer_price_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
    ];
    static $labels = [
        'price' => 'Цена',
        'item_offer_price_type_id' => 'Тип цены',
        'item_offer_id' => 'Товар',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Цены',
            'cols' => [
                'item_offer_price_type_id',
                'price',
            ]
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                [
                    'price',
                ],
                [
                    'item_offer_price_type_id',
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
            'type' => [
                'model' => 'Ecommerce\Item\Offer\Price\Type',
                'col' => 'item_offer_price_type_id'
            ],
        ];
    }

}
