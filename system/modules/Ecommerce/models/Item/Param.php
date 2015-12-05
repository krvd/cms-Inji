<?php

/**
 * Item param
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Param extends \Model
{
    static $objectName = 'Параметр товара';
    static $labels = [
        'item_option_id' => 'Параметр',
        'item_id' => 'Товар',
        'value' => 'Значение',
    ];
    static $cols = [
        'item_option_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'option'],
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'value' => ['type' => 'dynamicType', 'typeSource' => 'selfMethod', 'selfMethod' => 'realType'],
    ];

    function realType()
    {
        $type = $this->option->type;
        if ($type == 'select') {
            return [
                'type' => 'select',
                'source' => 'relation',
                'relation' => 'option:items',
            ];
        }
        return $type;
    }

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

    function value($default = '')
    {
        if ($this->option->type != 'select') {
            return $this->value;
        } elseif ($this->optionItem) {
            return $this->optionItem->value;
        }
        return $default;
    }

    static function relations()
    {
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
            'optionItem' => [
                'model' => 'Ecommerce\Item\Option\Item',
                'col' => 'value'
            ]
        ];
    }

}
