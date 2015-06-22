<?php

namespace TextBlocks;

class Block extends \Model {

    public static $objectName = "Текстовый блок";
    public static $cols = [
        'code' => [
            'type' => 'text'
        ],
        'name' => [
            'type' => 'text'
        ],
        'text' => [
            'type' => 'html'
        ],
        'date_create' => [
            'type' => 'currentDateTime'
        ]
    ];
    public static $labels = [
        'code' => 'код',
        'name' => 'Название',
        'text' => 'Текст',
        'date_create' => 'Дата создания'
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
                'code',
                'date_create',
            ]
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
                ['name', 'code'],
                ['text'],
            ]
        ]
    ];

    public static function relations() {
        return [
        ];
    }

}
