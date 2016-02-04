<?php

/**
 * Migration Map
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Migration;

class Map extends \Model
{
    public static $objectName = 'Карта миграции данных';
    public static $labels = [
        'name' => 'Название',
        'migration_id' => 'Миграция данных'
    ];
    public static $cols = [
        'name' => ['type' => 'text'],
        'migration_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'migration'],
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Карты миграции данных',
            'cols' => ['name', 'migration_id'],
            'actions' => [
                'mapEdit' => [
                    'className' => 'Href',
                    'href' => '/admin/migrations/map',
                    'text' => '<i class = "glyphicon glyphicon-cog"></i>'
                ],
                'Edit', 'Delete'
            ],
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'migration_id']
            ]
        ]
    ];

    public static function relations()
    {
        return [
            'migration' => [
                'model' => 'Migrations\Migration',
                'col' => 'migration_id'
            ],
            'paths' => [
                'type' => 'many',
                'model' => 'Migrations\Migration\Map\Path',
                'col' => 'migration_map_id'
            ]
        ];
    }

}
