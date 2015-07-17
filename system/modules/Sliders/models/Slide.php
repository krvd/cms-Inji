<?php

namespace Sliders;

class Slide extends \Model {

    public static $objectName = "Слайд";
    public static $cols = [
        'name' => [
            'type' => 'text'
        ],
        'description' => [
            'type' => 'textarea'
        ],
        'image_id' => [
            'type' => 'image'
        ],
        'date_create' => [
            'type' => 'dateTime',
        ],
        'sort' => [
            'type' => 'text'
        ],
        'slider_id' => [
            'type' => 'select',
            'source' => 'relation',
            'relation' => 'slider',
            'showCol' => 'name'
        ]
    ];
    public static $labels = [
        'name' => 'Имя',
        'description' => 'Описание',
        'image_id' => 'Изображение',
        'date_create' => 'Дата создания',
        'sort' => 'Сортировка',
        'slider_id' => 'Слайдер'
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Слайды',
            'cols' => [
                'name',
                'date_create'
            ]
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['image_id'],
                ['description'],
            ],
        ],
    ];

    public static function relations() {
        return [
            'slider' => [
                'model' => 'Sliders\Slider',
                'col' => 'slider_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_id'
            ]
        ];
    }

}
