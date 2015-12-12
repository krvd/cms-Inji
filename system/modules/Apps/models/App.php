<?php

/**
 * App
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Apps;

class App extends \Model
{
    /**
     * Model options
     */
    static $objectName = 'App options';
    static $storage = ['type' => 'moduleConfig', 'options' => ['share' => true]];
    static $labels = [
        'id' => '#',
        'name' => 'Название',
        'dir' => 'Директория',
        'installed' => 'Установлен',
        'default' => 'По умолчанию',
        'route' => 'Роут',
    ];
    static $cols = [
        'id' => ['type' => 'pk'],
        'name' => ['type' => 'text'],
        'dir' => ['type' => 'text'],
        'installed' => ['type' => 'bool'],
        'default' => ['type' => 'bool'],
        'route' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'setup' => [
            'name' => 'Настроенные приложения',
            'options' => [
                'access' => [
                    'apps' => [
                        'setup'
                    ]
                ]
            ],
            'cols' => [
                'name',
                'dir',
                'installed',
                'default',
                'route',
            ],
            'rowButtons' => [
                ['href' => '/setup/apps/configure', 'text' => '<i class = "glyphicon glyphicon-cog"></i>'], 'edit', 'delete'
            ],
        ]
    ];
    static $forms = [
        'manager' => [
            'name' => 'Приложение',
            'map' => [
                ['name', 'dir'],
                ['installed', 'default'],
                ['route'],
            ]
        ]
    ];

    static function index()
    {
        return 'id';
    }

}
