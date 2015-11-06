<?php

/**
 * Merchant
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Merchants;

class Merchant extends \Model
{
    static $objectName = 'Система оплаты';
    static $cols = [
        'name' => ['type' => 'text'],
        'object_name' => ['type' => 'text'],
        'image_file_id' => ['type' => 'image'],
        'active' => ['type' => 'bool'],
        'config' => ['type' => 'dataManager', 'relation' => 'configs']
    ];
    static $labels = [
        'name' => 'Название',
        'image_file_id' => 'Иконка',
        'active' => 'Активировано',
        'object_name' => 'Класс обработчика',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Системы оплаты',
            'cols' => [
                'name',
                'object_name',
                'active'
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name','image_file_id'],
                ['object_name', 'active'],
                ['config']
            ]
    ]];

    static function relations()
    {
        return [
            'configs' => [
                'type' => 'many',
                'model' => 'Merchants\Merchant\Config',
                'col' => 'merchant_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

}
