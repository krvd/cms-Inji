<?php

namespace Materials;

class Category extends \Model {

    static $objectModel = 'Категория';
    static $treeCategory = 'Materials\Material';
    static $labels = [
        'name' => 'Название',
        'description' => 'Описание',
        'image' => 'Изображение',
        'parent_id' => 'Родитель',
        'chpu' => 'Алиас',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'description' => ['type' => 'html'],
        'chpu' => ['type' => 'text'],
        'image' => ['type' => 'image'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent', 'showCol' => 'name']
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
                'parent_id',
            ],
        ]
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
                ['name', 'parent_id'],
                ['chpu', 'image'],
                ['description'],
            ]
        ]
    ];

    function beforeDelete() {
        foreach ($this->childs as $child) {
            $child->delete();
        }
    }

    static function relations() {
        return [
            'parent' => [
                'model' => 'Materials\Category',
                'col' => 'parent_id'
            ],
            'childs' => [
                'type' => 'many',
                'model' => 'Materials\Category',
                'col' => 'parent_id'
            ]
        ];
    }

}
