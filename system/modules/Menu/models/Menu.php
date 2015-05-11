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
    static $storageType = 'moduleConfig';
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name' => 'text',
                'code' => 'text',
                'items' => ['relation' => 'items']
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'inputs' => [
                'name' => 'text',
                'code' => 'text',
                'items' => ['relation' => 'items']
            ],
            'map'=>[
                ['name','code']
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
