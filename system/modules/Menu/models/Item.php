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
    static $storageType = 'moduleConfig';
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'type' => [ 'type' => 'select', 'source' => 'array', 'sourceArray' => ['href' => 'Ссылка', 'module' => 'Модуль']],
                'name' => 'text',
                'href' => 'text',
                'Menu_id' => ['relation' => 'menu', 'showCol' => 'name'],
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'inputs' => [
                'name' => 'text',
                'href' => 'text',
                'type' => [ 'type' => 'select', 'source' => 'array', 'sourceArray' => ['href' => 'Ссылка', 'module' => 'Модуль']],
                'Menu_id' => ['relation' => 'menu', 'showCol' => 'name'],
            ],
            'map'=>[
                ['type','Menu_id'],
                ['name','href']
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
