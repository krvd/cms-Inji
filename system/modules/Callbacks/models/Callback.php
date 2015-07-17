<?php

namespace Callbacks;

class Callback extends \Model {

    public static $objectName = "Отзыв";
    public static $cols = [
        'name' => [
            'type' => 'text'
        ],
        'text' => [
            'type' => 'html'
        ],
        'date_create' => [
            'type' => 'dateTime',
            'view' => [
                'type' => 'moduleMethod',
                'module' => 'Offers',
                'method' => 'toRusDate'
            ]
        ]
    ];
    public static $labels = [
        'name' => 'Имя',
        'text' => 'Текст',
        'date_create' => 'Дата создания'
    ];

    public static function relations() {
        return [
        ];
    }

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
                'name', 'text', 'date_create'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['text'],
            ]
        ]
    ];

}
