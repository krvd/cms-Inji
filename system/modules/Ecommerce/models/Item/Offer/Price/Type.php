<?php
/**
 * Item offer price type
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Ecommerce\Item\Offer\Price;

class Type extends \Model
{
    static $objectName = 'Тип цены';
    static $cols = [
        'name' => ['type' => 'text'],
        'curency' => ['type' => 'text'],
    ];
    static $labels = [
        'name' => 'Название',
        'curency' => 'Валюта',
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'curency'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'curency']
            ]
        ]
    ];

}
