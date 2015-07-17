<?php

namespace Materials;

class Material extends \Model {

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
        'keywords' => 'Ключевые слова'
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'alias',
                'category_id',
            ],
            'categorys' => [
                'model' => 'Materials\Category',
            ]
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
        'category_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'category', 'showCol' => 'category_name'],
        'image_file_id' => ['type' => 'image'],
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
            ]
        ]
    ];

    static function relations() {
        return [
            'category' => [
                'model' => '\Materials\Category',
                'col' => 'category_id'
            ],
            'image' => [
                'model' => '\Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

}
