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
    static $objectName = 'Категория магазина';
    static $treeCategory = 'Ecommerce\Item';
    static $cols = [
        'name' => ['type' => 'text'],
        'alias' => ['type' => 'text'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent'],
        'image_file_id' => ['type' => 'image'],
        'description' => ['type' => 'html'],
        'options_inherit' => ['type' => 'bool'],
        'options' => ['type' => 'dynamicList', 'relation' => 'options'],
        'viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsCategoryList', 'module' => 'Ecommerce'],
        'template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesCategoryList', 'module' => 'Ecommerce'],
        'weight' => ['type' => 'number']
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
                ['parent_id', 'image_file_id'],
                ['viewer', 'template'],
                //['options_inherit'],
                //['options'],
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

    function resolveTemplate()
    {
        if ($this->template !== 'inherit') {
            return $this->template;
        } elseif ($this->template == 'inherit' && $this->category) {
            return $this->category->resolveTemplate(true);
        } else {
            return 'current';
        }
    }

    function resolveViewer()
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
