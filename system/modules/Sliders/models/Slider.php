<?php

namespace Sliders;

class Slider extends \Model {

    public static $objectName = "Слайдер";
    public static $cols = [
        'name' => [
            'type' => 'text'
        ],
        'slides' => ['type' => 'select', 'source' => 'relation', 'relation' => 'slides'],
        'date_create' => [
            'type' => 'dateTime'
        ],
    ];
    public static $labels = [
        'name' => 'Название',
        'date_create' => 'Дата создания слайдера',
        'slides' => 'Слайды'
    ];
    static $dataManagers = [
        'manager' => [
            'name'=>'Слайдеры',
            'cols' => [
                'name', 'slides'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name']
            ]
        ]
    ];

    static function relations() {
        return [
            'slides' => [
                'type' => 'many',
                'model' => 'Sliders\Slide',
                'col' => 'slider_id'
            ]
        ];
    }

}
