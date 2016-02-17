<?php

/**
 * Item option relation
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item\Option;

class Relation extends \Model
{
    public static $objectName = 'Связь каталога и опций';
    public static $cols = [
        //Основные параметры
        'category_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'category'],
        'item_option_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'option'],
        //Системные
        'weight' => ['type' => 'number'],
        'date_create' => ['type' => 'dateTime']
    ];

    public static function relations()
    {
        return [
            'category' => [
                'model' => 'Ecommerce\Category',
                'col' => 'category_id'
            ],
            'option' => [
                'model' => 'Ecommerce\Item\Option',
                'col' => 'item_option_id'
            ],
        ];
    }

}
