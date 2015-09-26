<?php

namespace Sliders;

class Slide extends \Model
{
    public static $objectName = "Слайд";
    public static $cols = [
        'name' => [
            'type' => 'text'
        ],
        'description' => [
            'type' => 'html'
        ],
        'image_file_id' => [
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
        ],
        'user_id' => [
            'type' => 'select',
            'source' => 'relation',
            'relation' => 'user'
        ],
        'weight' => [
            'type' => 'number'
        ],
    ];
    public static $labels = [
        'name' => 'Имя',
        'description' => 'Описание',
        'date_create' => 'Дата создания',
        'sort' => 'Сортировка',
        'slider_id' => 'Слайдер',
        'user_id' => 'Создатель',
        'weight' => 'Вес',
        'image_file_id' => 'Изображение',
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
                ['image_file_id'],
                ['description'],
            ],
        ],
    ];

    public static function relations()
    {
        return [
            'slider' => [
                'model' => 'Sliders\Slider',
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
