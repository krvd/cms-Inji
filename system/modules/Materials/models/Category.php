<?php
/**
 * Category
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Materials;

class Category extends \Model
{
    static $objectModel = 'Категория';
    static $treeCategory = 'Materials\Material';
    static $labels = [
        'name' => 'Название',
        'description' => 'Описание',
        'image_file_id' => 'Изображение',
        'parent_id' => 'Родитель',
        'alias' => 'Алиас',
        'viewer' => 'Тип категории по умолчанию',
        'template' => 'Шаблон категории по умолчанию',
        'material_viewer' => 'Тип страниц по умолчанию',
        'material_template' => 'Шаблон страниц по умолчанию',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'description' => ['type' => 'html'],
        'alias' => ['type' => 'text'],
        'image_file_id' => ['type' => 'image'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent', 'showCol' => 'name'],
        'viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsCategoryList', 'module' => 'Materials'],
        'template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesCategoryList', 'module' => 'Materials'],
        'material_viewer' => ['type' => 'select', 'source' => 'method', 'method' => 'viewsList', 'module' => 'Materials'],
        'material_template' => ['type' => 'select', 'source' => 'method', 'method' => 'templatesList', 'module' => 'Materials'],
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
                'alias',
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
                ['alias', 'image_file_id'],
                ['viewer', 'template'],
                ['material_viewer', 'material_template'],
                ['description'],
            ]
        ]
    ];

    function beforeDelete()
    {
        foreach ($this->childs as $child) {
            $child->delete();
        }
    }

    static function relations()
    {
        return [
            'parent' => [
                'model' => 'Materials\Category',
                'col' => 'parent_id'
            ],
            'childs' => [
                'type' => 'many',
                'model' => 'Materials\Category',
                'col' => 'parent_id'
            ],
            'items' => [
                'type' => 'many',
                'model' => 'Materials\Material',
                'col' => 'category_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

    function resolveTemplate($material = false)
    {
        $param = $material ? 'material_template' : 'template';
        if ($this->$param !== 'inherit') {
            return $this->$param;
        } elseif ($this->$param == 'inherit' && $this->parent) {
            return $this->parent->resolveTemplate($material);
        } else {
            return 'current';
        }
    }

    function resolveViewer($material = false)
    {
        $param = $material ? 'material_viewer' : 'viewer';
        if ($this->$param !== 'inherit') {
            return $this->$param;
        } elseif ($this->$param == 'inherit' && $this->parent) {
            return $this->parent->resolveViewer($material);
        } else {
            return $material ? 'default' : 'category';
        }
    }

}
