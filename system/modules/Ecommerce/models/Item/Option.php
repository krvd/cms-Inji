<?php

/**
 * Item option
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Option extends \Model
{
    public static $objectName = 'Свойство';
    public static $cols = [
        //Основные параметры
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'postfix' => ['type' => 'text'],
        'default_val' => ['type' => 'text'],
        'view' => ['type' => 'bool'],
        'searchable' => ['type' => 'bool'],
        //Системные
        'weight' => ['type' => 'number'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'advance' => ['type' => 'text'],
        'date_create' => ['type' => 'dateTime']
    ];
    public static $labels = [
        'name' => 'Название',
        'code' => 'Код',
        'type' => 'Тип',
        'postfix' => 'Постфикс',
        'default_val' => 'Значение по умолчанию',
        'view' => 'Отображается',
        'searchable' => 'Используется при поиске',
        'weight' => 'Вес сортировки',
        'advance' => 'Дополнительные параметры',
        'user_id' => 'Создатель',
        'date_create' => 'Дата создания'
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Свойства товаров',
            'cols' => [
                'name', 'code', 'type', 'view', 'searchable', 'user_id', 'date_create'
            ]
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'code', 'type'],
                ['default_val', 'postfix'],
                ['view', 'searchable'],
            ]
        ]
    ];

    public static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'items' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Option\Item',
                'col' => 'item_option_id'
            ]
        ];
    }

    public function beforeSave()
    {
        if (!isset($this->id)) {
            $this->user_id = \Users\User::$cur->id;
        }
    }

}
