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

namespace Callbacks\Callback;

/**
 * Description of Type
 *
 * @author inji
 */
class Type extends \Model
{
    public static $objectName = "Тип отзыва";
    public static $cols = [
        'name' => ['type' => 'text'],
        'date_create' => ['type' => 'dateTime',]
    ];
    public static $labels = [
        'name' => 'Название',
        'date_create' => 'Дата создания'
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'date_create'
            ],
            'filters' => [
                'name'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
            ]
        ]
    ];

}
