<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Offer extends \Model {

    static $objectName = 'Торговое предложение';
    static $cols = [
        'name' => ['type' => 'text'],
        'article' => ['type' => 'text'],
        'warehouses' => ['type' => 'select', 'source' => 'relation', 'relation' => 'warehouses'],
        'prices' => ['type' => 'select', 'source' => 'relation', 'relation' => 'prices'],
    ];
    static $labels = [
        'name' => 'Название',
        'article' => 'Артикул',
        'warehouses' => 'Наличие на складах',
        'prices' => 'Цены',
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'article', 'warehouses', 'prices'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'article'],
                ['warehouses'],
                ['prices']
            ]
        ]
    ];

    static function relations() {
        return [
            'warehouses' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Warehouse',
                'col' => 'item_offer_id'
            ],
            'prices' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Price',
                'col' => 'item_offer_id',
            ],
        ];
    }

}
