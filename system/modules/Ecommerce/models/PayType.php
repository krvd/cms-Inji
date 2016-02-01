<?php

/**
 * Pay Type
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class PayType extends \Model
{
    public static $objectName = 'Оплата';
    public static $labels = [
        'name' => 'Название',
        'info' => 'Дополнительная информация',
        'handler' => 'Обработчик',
        'icon_file_id' => 'Иконка',
    ];
    public static $cols = [
        'name' => ['type' => 'text'],
        'info' => ['type' => 'textarea'],
        'handler' => [
            'type' => 'select',
            'source' => 'method',
            'module' => 'Ecommerce',
            'method' => 'getPayTypeHandlers',
            'params' => [true]
        ],
        'icon_file_id' => ['type' => 'image'],
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Способы оплаты',
            'cols' => [
                'name', 'handler'
            ],
        ],
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'icon_file_id'],
                ['handler', 'info']
            ]
    ]];

    public static function relations()
    {
        return [
            'icon' => [
                'model' => 'Files\File',
                'col' => 'icon_file_id'
            ],
        ];
    }

}
