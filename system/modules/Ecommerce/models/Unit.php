<?php

/**
 * Unit
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Unit extends \Model
{
    static $objectName = 'Единица измерения';
    static $labels = [
        'name' => 'Название',
        'code' => 'Код',
        'international' => 'Международное обозначение',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'international' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Единицы измерения',
            'cols' => [
                'name', 'code', 'international'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'code', 'international']
            ]
        ]
    ];

}
