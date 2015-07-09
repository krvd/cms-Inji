<?php

namespace Materials;

class Material extends \Model {

    static $objectName = 'Материал';
    static $categoryModel = 'Materials\Catalog';
    static $labels = [
        'name' => 'Заголовок',
        'catalog_id' => 'Раздел',
        'preview' => 'Краткое превью',
        'text' => 'Текст страницы',
        'chpu' => 'Алиас страницы',
        'template' => 'Шаблон сайта',
        'viewer' => 'Тип страницы',
        'image_file_id' => 'Фото материала'
    ];
    static $dataManagers = [
        'manager' => [
            'options' => [
                'access' => [
                    'groups' => [
                        3
                    ]
                ]
            ],
            'cols' => [
                'name',
                'chpu',
                'catalog_id',
            ],
            'categorys' => [
                'model' => 'Materials\Catalog',
            ]
        ]
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'chpu' => ['type' => 'text'],
        'viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsList', 'module' => 'Materials'],
        'template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesList', 'module' => 'Materials'],
        'preview' => ['type' => 'html'],
        'text' => ['type' => 'html'],
        'catalog_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'catalog', 'showCol' => 'catalog_name'],
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
                ['name', 'catalog_id'],
                ['chpu', 'image_file_id'],
                ['template', 'viewer'],
                ['preview'],
                ['text'],
            ]
        ]
    ];

    static function relations() {
        return [
            'catalog' => [
                'model' => '\Materials\Catalog',
                'col' => 'catalog_id'
            ],
            'image' => [
                'model' => '\Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

}
