<?php

/**
 * Menu
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Menu;

class Menu extends \Model
{
    public static $objectName = 'Меню';
    public static $labels = [
        'name' => 'Название',
        'code' => 'Алиас',
        'items' => 'Пункты меню'
    ];
    public static $storage = ['type' => 'moduleConfig'];
    public static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'items']
    ];
    public static $dataManagers = [
        'manager' => [
            'options' => [
                'access' => [
                    'groups' => [
                        3
                    ]
                ]
            ],
            'cols' => [
                'name', 'code', 'items'
            ]
        ]
    ];
    public static $forms = [
        'manager' => [
            'options' => [
                'access' => [
                    'groups' => [
                        3
                    ]
                ]
            ],
            'map' => [
                ['name', 'code']
            ]
        ]
    ];

    public static function relations()
    {
        return [
            'items' => [
                'type' => 'many',
                'model' => 'Menu\Item',
                'col' => 'Menu_id'
            ]
        ];
    }

    public static function index()
    {
        return 'id';
    }

}
