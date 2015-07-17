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

namespace Menu;

class Menu extends \Model {

    static $objectName = 'Меню';
    static $labels = [
        'name' => 'Название',
        'code' => 'Алиас',
        'items' => 'Пункты меню'
    ];
    static $storage = ['type' => 'moduleConfig'];
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'items']
    ];
    static $dataManagers = [
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
    static $forms = [
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

    static function relations() {
        return [
            'items' => [
                'type' => 'many',
                'model' => 'Menu\Item',
                'col' => 'Menu_id'
            ]
        ];
    }

    static function index() {
        return 'id';
    }

}
