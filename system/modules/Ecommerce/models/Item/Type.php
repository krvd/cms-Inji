<?php

/**
 * Item type
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Type extends \Model
{
    public static $objectName = 'Тип товара';
    public static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'electronic' => ['type' => 'bool'],
    ];
    public static $labels = [
        'name' => 'Название',
        'code' => 'Код',
        'electronic' => 'Электронный',
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'code',
                'electronic'
            ]
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['code', 'electronic']
            ]
        ]
    ];

}
