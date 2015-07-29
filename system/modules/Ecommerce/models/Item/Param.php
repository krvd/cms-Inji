<?php

/**
 * Item Param model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace Ecommerce\Item;

class Param extends \Model {

    static $objectName = 'Параметр товара';
    static $labels = [
        'item_option_id' => 'Параметр',
        'item_id' => 'Товар',
        'value' => 'Значение',
    ];
    static $cols = [
        'item_option_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'option'],
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'value' => ['type' => 'text'],
    ];
    static $dataManagers = [
        
        'manager' => [
            'name' => 'Параметры товара',
            'cols' => [
                'item_option_id',
                'item_id',
                'value',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['item_id', 'item_option_id'],
                ['value']
            ]
    ]];

    static function relations() {
        return [
            'file' => [
                'model' => 'Files\File',
                'col' => 'value'
            ],
            'option' => [
                'model' => 'Ecommerce\Item\Option',
                'col' => 'item_option_id'
            ],
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ],
        ];
    }

}
