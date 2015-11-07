<?php

namespace Ecommerce;

class Category extends \Model
{
    static $objectName = 'Категория магазина';
    static $treeCategory = 'Ecommerce\Item';
    static $cols = [
        'name' => ['type' => 'text'],
        'alias' => ['type' => 'text'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent'],
        'image_file_id' => ['type' => 'image'],
        'description' => ['type' => 'html'],
        'options_inherit' => ['type' => 'bool'],
        'options' => ['type' => 'dynamicList', 'relation' => 'options']
    ];
    static $labels = [
        'name' => 'Название',
        'alias' => 'Алиас',
        'parent_id' => 'Родитель',
        'image_file_id' => 'Изображение',
        'description' => 'Описание',
        'options_inherit' => 'Наследовать набор свойств',
        'options' => 'Свойства товаров',
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'alias'],
                ['parent_id', 'image_file_id', 'options_inherit'],
                ['options'],
                ['description']
            ]
        ]
    ];

    static function relations()
    {
        return [
            'items' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item',
                'col' => 'category_id',
            ],
            'parent' => [
                'model' => 'Ecommerce\Category',
                'col' => 'parent_id'
            ],
            'options' => [
                'type' => 'relModel',
                'model' => 'Ecommerce\Item\Option',
                'relModel' => 'Ecommerce\Item\Option\Relation',
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ],
            'catalogs' => [
                'type' => 'many',
                'model' => 'Ecommerce\Category',
                'col' => 'parent_id',
            ]
        ];
    }

}
