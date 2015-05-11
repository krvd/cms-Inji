<?php

/*
 * The MIT License
 *
 * Copyright 2015 Alexey Krupskiy <admin@inji.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Description of MaterialCatalog
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class MaterialCatalog extends Model
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
            Inji::app()->db->query('UPDATE
                ' . Inji::app()->db->table_prefix . 'materials_catalogs 
                    SET 
                        mc_tree_path = REPLACE(mc_tree_path, "' . $oldPath . $this->mc_id . '/' . '", "' . $this->mc_tree_path . $this->mc_id . '/' . '") 
                    WHERE mc_tree_path LIKE "' . $oldPath . $this->mc_id . '/' . '%"');

            Inji::app()->db->query('UPDATE
                ' . Inji::app()->db->table_prefix . 'materials
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
