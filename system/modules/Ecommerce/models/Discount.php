<?php

/**
 * Discount
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Discount extends \Model
{
    static $objectName = 'Скидка';
    static $cols = [
        'name' => ['type' => 'text'],
        'type' => ['type' => 'select', 'source' => 'array', 'sourceArray' => ['sum' => 'Сумма', 'procent' => 'Процент']],
        'amount' => ['type' => 'text'],
        'condition' => ['type' => 'json'],
    ];
    static $labels = [
        'name' => 'Название',
        'type' => 'Тип',
        'amount' => 'Значение',
        'condition' => 'Условие',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Скидки',
            'cols' => [
                'name',
                'type',
                'amount',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['type', 'amount'],
            //['condition']
            ]
    ]];

}
