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

class Item extends \Model {

    static $objectName = 'Пункт меню';
    static $labels = [
        'type' => 'Тип',
        'name' => 'Название',
        'href' => 'Ссылка',
        'Menu_id' => 'Меню'
    ];
    static $storage = ['type' => 'moduleConfig'];
    static $cols = [
                'type' => ['type' => 'select', 'source' => 'array', 'sourceArray' => ['href' => 'Ссылка']],
                'name' => ['type' => 'text'],
                'href' => ['type' => 'text'],
                'Menu_id' => ['type' => 'select', 'source'=>'relation', 'relation' => 'menu', 'showCol' => 'name'],
                    
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'type',
                'name',
                'href',
                'Menu_id'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['type', 'Menu_id'],
                ['name', 'href']
            ]
        ]
    ];

    static function relations() {
        return [
            'menu' => [
                'model' => 'Menu\Menu',
                'col' => 'Menu_id'
            ]
        ];
    }

    static function index() {
        return 'id';
    }

}
