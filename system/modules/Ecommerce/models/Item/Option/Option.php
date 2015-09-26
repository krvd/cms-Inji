<?php

namespace Ecommerce\Item;

class Option extends \Model
{
    static $objectName = 'Свойство';
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'postfix' => ['type' => 'text'],
        'default_val' => ['type' => 'text'],
        'view' => ['type' => 'bool'],
        'searchable' => ['type' => 'bool'],
        'weight' => ['type' => 'Number'],
        'advance' => ['type' => 'text'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'date_create' => ['type' => 'dateTime']
    ];
    static $labels = [
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
    static $dataManagers = [
        'manager' => [
            'name' => 'Свойства товаров',
            'cols' => [
                'name', 'code', 'type', 'view', 'searchable', 'user_id', 'date_create'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'code', 'type'],
                ['default_val', 'postfix'],
                ['view', 'searchable'],
            ]
        ]
    ];

    static function relations()
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

    function beforeSave()
    {
        if (!isset($this->id)) {
            $this->user_id = \Users\User::$cur->id;
        }
    }

}
