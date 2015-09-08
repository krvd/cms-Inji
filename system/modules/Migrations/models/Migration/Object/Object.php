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

namespace Migrations\Migration;

class Object extends \Model {

    static $objectName = 'Объект миграции';
    static $labels = [
        'name' => 'Название',
        'migration_id' => 'Миграция данных'
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'migration_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'migration'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Объекты миграции',
            'cols' => ['name', 'code', 'type', 'migration_id']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'migration_id'],
                ['code', 'type'],
            ]
        ]
    ];

    static function relations() {
        return [
            'migration' => [
                'model' => 'Migrations\Migration',
                'col' => 'migration_id'
            ],
            'params' => [
                'type' => 'many',
                'model' => 'Migrations\Migration\Object\Param',
                'col' => 'object_id',
                'where' => [
                    ['parent_id', 0]
                ]
            ]
        ];
    }

}
