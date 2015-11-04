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

class Item extends \Model
{
    static $objectName = 'Пункт меню';
    static $labels = [
        'type' => 'Тип',
        'name' => 'Название',
        'tooltip' => 'Подсказка',
        'href' => 'Ссылка',
        'Menu_id' => 'Меню',
        'parent_id' => 'Дочерний пункт'
    ];
    static $storage = ['type' => 'moduleConfig'];
    static $cols = [
        'type' => ['type' => 'select', 'source' => 'array', 'sourceArray' => [
                'href' => 'Ссылка',
                'materialCategory' => [
                    'text' => 'Категория материалов',
                    'input' => [
                        'type' => 'select',
                        'source' => 'model',
                        'model' => '\Materials\Category',
                        'name' => 'aditional'
                    ]
                ],
                'material' => [
                    'text' => 'Материал',
                    'input' => [
                        'type' => 'select',
                        'source' => 'model',
                        'model' => '\Materials\Material',
                        'name' => 'aditional'
                    ]
                ]
            ]
        ],
        'aditional' => ['type' => 'hidden'],
        'name' => ['type' => 'text'],
        'href' => ['type' => 'text'],
        'tooltip' => ['type' => 'text'],
        'weight' => ['type' => 'number'],
        'Menu_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'menu', 'showCol' => 'name'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'type',
                'name',
                'tooltip',
                'href',
                'Menu_id'
            ],
            'sortMode' => true
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['aditional'],
                ['type', 'Menu_id'],
                ['name', 'href'],
                ['tooltip']
            ]
        ]
    ];

    static function relations()
    {
        return [
            'menu' => [
                'model' => 'Menu\Menu',
                'col' => 'Menu_id'
            ]
        ];
    }

    static function index()
    {
        return 'id';
    }

}
