<?php

/**
 * Item Category
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Category extends \Model
{
    public static $objectName = 'Категория магазина';
    public static $treeCategory = 'Ecommerce\Item';
    public static $cols = [
        //Основные параметры
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent'],
        'name' => ['type' => 'text'],
        'alias' => ['type' => 'text'],
        'viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsCategoryList', 'module' => 'Ecommerce'],
        'template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesCategoryList', 'module' => 'Ecommerce'],
        'description' => ['type' => 'html'],
        'image_file_id' => ['type' => 'image'],
        'options_inherit' => ['type' => 'bool'],
        //Системные
        'imported' => ['type' => 'bool'],
        'weight' => ['type' => 'number'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'tree_path' => ['type' => 'text'],
        'date_create' => ['type' => 'dateTime'],
        //Менеджеры
        'options' => ['type' => 'dataManager', 'relation' => 'options'],
    ];
    public static $labels = [
        'name' => 'Название',
        'alias' => 'Алиас',
        'parent_id' => 'Родительская категория',
        'image_file_id' => 'Изображение',
        'description' => 'Описание',
        'options_inherit' => 'Наследовать набор свойств',
        'options' => 'Свойства товаров',
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'alias'],
                ['parent_id', 'image_file_id'],
                ['viewer', 'template'],
                ['options_inherit'],
                ['options'],
                ['description']
            ]
        ]
    ];

    public static function indexes()
    {
        return [
            'ecommerce_category_category_parent_id' => [
                'type' => 'INDEX',
                'cols' => [
                    'category_parent_id',
                ]
            ],
            'ecommerce_category_category_tree_path' => [
                'type' => 'INDEX',
                'cols' => [
                    'category_tree_path(255)'
                ]
            ],
        ];
    }

    public static function relations()
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
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'catalogs' => [
                'type' => 'many',
                'model' => 'Ecommerce\Category',
                'col' => 'parent_id',
            ]
        ];
    }

    public function beforeSave()
    {
        if ($this->id && $this->id == $this->parent_id) {
            $this->parent_id = 0;
            \Msg::add('Категория не может быть сама себе родителем');
        }
    }

    public function beforeDelete()
    {
        foreach ($this->catalogs as $category) {
            $category->delete();
        }
    }

    public function resolveTemplate()
    {
        if ($this->template !== 'inherit') {
            return $this->template;
        } elseif ($this->template == 'inherit' && $this->category) {
            return $this->category->resolveTemplate(true);
        } else {
            return 'current';
        }
    }

    public function resolveViewer()
    {
        if ($this->viewer !== 'inherit') {
            return $this->viewer;
        } elseif ($this->viewer == 'inherit' && $this->category) {
            return $this->category->resolveViewer(true);
        } else {
            return (!empty(\App::$cur->ecommerce->config['defaultCategoryView']) ? \App::$cur->ecommerce->config['defaultCategoryView'] : 'itemList');
        }
    }

}
