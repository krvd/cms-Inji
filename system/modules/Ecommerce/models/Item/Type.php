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
    static $objectName = 'Тип товара';
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'electronic' => ['type' => 'bool'],
    ];
    static $labels = [
        'name' => 'Название',
        'code' => 'Код',
        'electronic' => 'Электронный',
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'code',
                'electronic'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['code', 'electronic']
            ]
        ]
    ];

}
