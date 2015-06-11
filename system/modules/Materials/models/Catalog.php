<?php

namespace Materials;

class Catalog extends \Model {

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
                'model' => 'Materials\Catalog',
                'col' => 'parent_id'
            ],
            'childs' => [
                'type' => 'many',
                'model' => 'Materials\Catalog',
                'col' => 'parent_id'
            ]
        ];
    }

    function beforeSave() {
        $oldPath = $this->tree_path;
        $this->tree_path = $this->getCatalogTree($this);
        $itemsTable = \App::$cur->db->table_prefix . Material::table();
        $itemTreeCol = Material::colPrefix() . 'tree_path';

        $categoryTreeCol = $this->colPrefix() . 'tree_path';
        $categoryTable = \App::$cur->db->table_prefix . $this->table();
        if ($oldPath) {
            \App::$cur->db->query('UPDATE
                ' . $categoryTable . ' 
                    SET 
                        ' . $categoryTreeCol . ' = REPLACE(' . $categoryTreeCol . ', "' . $oldPath . $this->id . '/' . '", "' . $this->tree_path . $this->id . '/' . '") 
                    WHERE ' . $categoryTreeCol . ' LIKE "' . $oldPath . $this->id . '/' . '%"');

            \App::$cur->db->query('UPDATE
                ' . $itemsTable . '
                    SET 
                        ' . $itemTreeCol . ' = REPLACE(' . $itemTreeCol . ', "' . $oldPath . $this->id . '/' . '", "' . $this->tree_path . $this->id . '/' . '") 
                    WHERE ' . $itemTreeCol . ' LIKE "' . $oldPath . $this->id . '/' . '%"');
        }
        Material::update([$itemTreeCol => $this->tree_path . $this->id . '/'], [Material::colPrefix() . $this->colPrefix() . 'id', $this->id]);
    }

    function getCatalogTree($catalog) {
        if ($catalog && $catalog->parent) {
            if ($catalog->parent->tree_path) {
                return $catalog->parent->tree_path . $catalog->parent->id . '/';
            } else {
                return $this->getCatalogTree($catalog->parent) . $catalog->parent->id . '/';
            }
        }
        return '/';
    }

}
