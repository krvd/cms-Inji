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

namespace Migrations\Migration\Object;

class Param extends \Model {

    static $objectName = 'Параметр объекта миграции';
    static $labels = [
        'code' => 'Код',
        'type' => 'Тип',
        'object_id' => 'Миграция данных'
    ];
    static $cols = [
        'code' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'object_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'object'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Параметры объекта миграции',
            'cols' => ['code', 'type', 'object_id']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['code', 'type', 'object_id'],
            ]
        ]
    ];

    static function relations() {
        return [
            'object' => [
                'model' => 'Migrations\Migration\Object',
                'col' => 'object_id'
            ],
            'values' => [
                'type' => 'many',
                'model' => 'Migrations\Migration\Object\Param\Value',
                'col' => 'param_id'
            ]
        ];
    }

}