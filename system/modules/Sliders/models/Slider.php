<?php

namespace Sliders;

class Slider extends \Model
{
    public static $objectName = "Слайдер";
    public static $cols = [
        'name' => [
            'type' => 'text'
        ],
        'description' => [
            'type' => 'html'
        ],
        'user_id' => [
            'type' => 'select',
            'source' => 'relation',
            'relation' => 'user'
        ],
        'image_file_id' => [
            'type' => 'image'
        ],
        'date_create' => [
            'type' => 'dateTime'
        ],
        'slides' => [
            'type' => 'select',
            'source' => 'relation',
            'relation' => 'slides'
        ],
    ];
    public static $labels = [
        'name' => 'Название',
        'date_create' => 'Дата создания слайдера',
        'slides' => 'Слайды',
        'description' => 'Описание',
        'user_id' => 'Создатель',
        'image_file_id' => 'Изображение',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Слайдеры',
            'cols' => [
                'name', 'slides', 'user_id', 'date_create'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'image_file_id'],
                ['description']
            ]
        ]
    ];

    static function relations()
    {
        return [
            'slides' => [
                'type' => 'many',
                'model' => 'Sliders\Slide',
                'col' => 'slider_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ],
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
