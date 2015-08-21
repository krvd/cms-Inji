<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Ecommerce\UserAdds;
class Field extends \Model {

    static $objectName = 'Поле информации при заказе';
    static $cols = [
        'name' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'required' => ['type' => 'bool'],
    ];
    static $labels = [
        'name' => 'Название',
        'type' => 'Тип',
        'required' => 'Обязательно'
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'type', 'required'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'type', 'required']
            ]
        ]
    ];

}