<?php
/**
 * Warehouse
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Ecommerce;

class Warehouse extends \Model
{
    static $objectName = 'Склад';
    static $labels = [
        'name' => 'Название',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Склады',
            'cols' => [
                'name',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
            ]
    ]];

}
