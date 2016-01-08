<?php

/**
 * Material
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Materials;

class Material extends \Model
{
    static $objectName = 'Материал';
    static $categoryModel = 'Materials\Category';
    static $labels = [
        'name' => 'Заголовок',
        'category_id' => 'Раздел',
        'preview' => 'Краткое превью',
        'text' => 'Текст страницы',
        'alias' => 'Алиас страницы',
        'template' => 'Шаблон сайта',
        'viewer' => 'Тип страницы',
        'image_file_id' => 'Фото материала',
        'description' => 'Описание для поисковиков',
        'keywords' => 'Ключевые слова',
        'user_id' => 'Создатель',
        'date_create' => 'Дата создания'
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'alias',
                'category_id',
                'date_create'
            ],
            'sortable' => [
                'name',
                'alias',
                'category_id',
                'date_create'
            ],
            'filters' => [
                'name',
                'preview',
                'text',
                'alias',
                'template',
                'viewer',
                'description',
                'keywords',
                'user_id',
                'date_create'
            ],
            'categorys' => [
                'model' => 'Materials\Category',
            ],
            'sortMode' => true
        ]
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'alias' => ['type' => 'text'],
        'description' => ['type' => 'text'],
        'keywords' => ['type' => 'text'],
        'viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsList', 'module' => 'Materials'],
        'template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesList', 'module' => 'Materials'],
        'preview' => ['type' => 'html'],
        'text' => ['type' => 'html'],
        'category_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'category'],
        'image_file_id' => ['type' => 'image'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'date_create' => ['type' => 'dateTime'],
        'link' => ['type' => 'dataManager', 'relation' => 'links'],
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
                ['name', 'category_id'],
                ['alias', 'image_file_id'],
                ['template', 'viewer'],
                ['keywords', 'description'],
                ['preview'],
                ['text'],
                ['link'],
            ]
        ]
    ];

    static function relations()
    {
        return [
            'category' => [
                'model' => '\Materials\Category',
                'col' => 'category_id'
            ],
            'image' => [
                'model' => '\Files\File',
                'col' => 'image_file_id'
            ],
            'user' => [
                'model' => '\Users\User',
                'col' => 'user_id'
            ],
            'links' => [
                'type' => 'many',
                'model' => '\Materials\Material\Link',
                'col' => 'material_id'
            ]
        ];
    }

    function getHref()
    {
        $href = !empty(\App::$primary->config['defaultModule']) && \App::$primary->config['defaultModule'] == 'Materials' ? '' : '/materials';
        $treePath = array_filter(explode('/', $this->tree_path));
        if ($treePath) {
            $categorys = Category::getList(['where' => ['id', implode(',', $treePath), 'IN']]);
            foreach ($categorys as $category) {
                $href .="/{$category->alias}";
            }
        }
        return $href . "/" . ($this->alias ? $this->alias : $this->pk());
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
            return 'default';
        }
    }

}
