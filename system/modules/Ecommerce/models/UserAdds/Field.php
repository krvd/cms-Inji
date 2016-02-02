<?php

/**
 * UserAdds info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\UserAdds;

class Field extends \Model
{
    public static $objectName = 'Поле информации при заказе';
    public static $cols = [
        'name' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'required' => ['type' => 'bool'],
        'save' => ['type' => 'bool'],
        'weight' => ['type' => 'number'],
    ];
    public static $labels = [
        'name' => 'Название',
        'type' => 'Тип',
        'required' => 'Обязательно',
        'save' => 'Сохраняется'
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'type', 'required', 'save'
            ],
            'sortMode'=>true
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'type'],
                ['required', 'save']
            ]
        ]
    ];

}
