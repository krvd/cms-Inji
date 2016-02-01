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
    public static $objectName = 'Склад';
    public static $labels = [
        'name' => 'Название',
    ];
    public static $cols = [
        'name' => ['type' => 'text'],
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Склады',
            'cols' => [
                'name',
            ],
        ],
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name'],
            ]
    ]];

}
