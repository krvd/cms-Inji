<?php

namespace Materials;

class Catalog extends \Model
{

    static $labels = [
        'mc_name' => 'Название',
        'mc_description' => 'Описание',
        'mc_image' => 'Изображение',
        'mc_parent_id' => 'Родитель',
        'mc_chpu' => 'Алиас',
    ];
    static $forms = [
        'manage' => [
            'options' => [
                'mc_name' => 'text',
                'mc_description' => 'html',
                'mc_chpu' => 'text',
                'mc_image' => 'image',
                'mc_parent_id' => ['relation' => 'parent', 'showCol' => 'mc_name']
            ],
            'map' => [
                ['mc_name', 'mc_parent_id'],
                ['mc_chpu', 'mc_image'],
                ['mc_description'],
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
                'model' => 'MaterialCatalog',
                'col' => 'mc_parent_id'
            ],
            'childs' => [
                'type' => 'many',
                'model' => 'MaterialCatalog',
                'col' => 'mc_parent_id'
            ]
        ];
    }

    static function colPrefix()
    {
        return 'mc_';
    }

    static function table()
    {
        return 'materials_catalogs';
    }

    static function index()
    {
        return 'mc_id';
    }

    function beforeSave()
    {
        $oldPath = $this->mc_tree_path;
        $this->mc_tree_path = $this->getCatalogTree($this);
        if ($oldPath) {
            App::$cur->db->query('UPDATE
                ' . App::$cur->db->table_prefix . 'materials_catalogs 
                    SET 
                        mc_tree_path = REPLACE(mc_tree_path, "' . $oldPath . $this->mc_id . '/' . '", "' . $this->mc_tree_path . $this->mc_id . '/' . '") 
                    WHERE mc_tree_path LIKE "' . $oldPath . $this->mc_id . '/' . '%"');

            App::$cur->db->query('UPDATE
                ' . App::$cur->db->table_prefix . 'materials
                    SET 
                        material_tree_path = REPLACE(material_tree_path, "' . $oldPath . $this->mc_id . '/' . '", "' . $this->mc_tree_path . $this->mc_id . '/' . '") 
                    WHERE material_tree_path LIKE "' . $oldPath . $this->mc_id . '/' . '%"');
        }
        Material::update(['material_tree_path' => $this->mc_tree_path . $this->mc_id . '/'], ['material_mc_id', $this->mc_id]);
    }

    function getCatalogTree($catalog)
    {
        if ($catalog && $catalog->parent) {
            if ($catalog->parent->mc_tree_path) {
                return $catalog->parent->mc_tree_path . $catalog->parent->mc_id . '/';
            } else {
                return $this->getCatalogTree($catalog->parent) . $catalog->parent->mc_id . '/';
            }
        }
        return '/';
    }

}
