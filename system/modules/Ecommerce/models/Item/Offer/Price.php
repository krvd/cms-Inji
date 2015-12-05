<?php

/**
 * Item offer price
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item\Offer;

class Price extends \Model
{
    static $objectName = 'Цена';
    static $cols = [
        'price' => ['type' => 'text'],
        'item_offer_price_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
    ];
    static $labels = [
        'price' => 'Цена',
        'item_offer_price_type_id' => 'Тип цены',
        'item_offer_id' => 'Товар',
        'currency_id' => 'Валюта',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Цены',
            'cols' => [
                'item_offer_price_type_id',
                'price',
                'currency_id',
            ]
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['price', 'currency_id',],
                ['item_offer_price_type_id', 'item_offer_id']
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
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
        ];
    }

}
