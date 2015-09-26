<?php

/**
 * Item type model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
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
