<?php

/**
 * Description of Material
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class Material extends Model
{

    static $labels = [
        'material_name' => 'Заголовок',
        'material_mc_id' => 'Раздел',
        'material_preview' => 'Краткое превью',
        'material_text' => 'Текст страницы',
        'material_chpu' => 'Алиас страницы',
        'material_template' => 'Шаблон сайта',
        'material_viewer' => 'Тип страницы'
    ];
    static $dataTable = [
        'cols' => [
            'material_name' => [],
            'material_chpu' => [],
            'material_mc_id' => ['relation' => 'catalog', 'showCol' => 'mc_name'],
        ],
        'categorys' => [
            'model' => 'MaterialCatalog',
        ]
    ];
    static $forms = [
        'manage' => [
            'options' => [
                'material_name' => 'text',
                'material_chpu' => 'text',
                'material_viewer' => [ 'type' => 'methodSource', 'method' => 'viewsList', 'module' => 'Materials'],
                'material_template' => [ 'type' => 'methodSource', 'method' => 'templatesList', 'module' => 'Materials'],
                'material_preview' => 'html',
                'material_text' => 'html',
                'material_mc_id' => ['relation' => 'catalog', 'showCol' => 'mc_name'],
            ],
            'map' => [
                ['material_name', 'material_mc_id'],
                ['material_chpu'],
                ['material_template', 'material_viewer'],
                ['material_preview'],
                ['material_text'],
            ]
        ]
    ];

    static function relations()
    {
        return [
            'catalog' => [
                'model' => 'MaterialCatalog',
                'col' => 'material_mc_id'
            ]
        ];
    }

    static function colPrefix()
    {
        return 'material_';
    }

    static function table()
    {
        return 'materials';
    }

    static function index()
    {
        return 'material_id';
    }

    function beforeSave()
    {
        if ($this->catalog) {
            $this->material_tree_path = $this->catalog->mc_tree_path . $this->catalog->mc_id . '/';
        } else {
            $this->material_tree_path = '/';
        }
    }

}
