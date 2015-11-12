<?php

namespace Users;

class Info extends \Model
{
    public static $objectName = "Расширеная информация";
    public static $cols = [
        'first_name' => [
            'type' => 'text'
        ],
        'last_name' => [
            'type' => 'text'
        ],
        'middle_name' => [
            'type' => 'text'
        ],
        'sex' => [
            'type' => 'select',
            'source' => 'array',
            'sourceArray' => [
                0 => 'Не определился',
                1 => 'Мужчина',
                2 => 'Женщина'
            ]
        ],
        'photo_file_id' => [
            'type' => 'image'
        ],
        'bday' => [
            'type' => 'date'
        ],
        'phone' => [
            'type' => 'text'
        ],
        'country' => [
            'type' => 'text'
        ],
        'city' => [
            'type' => 'text'
        ],
        'user_id' => [
            'type' => 'select',
            'source' => 'relation',
            'relation' => 'user',
            'showCol' => 'name'
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['first_name', 'middle_name', 'last_name'],
                ['country', 'city'],
                ['sex', 'photo_file_id'],
                ['bday', 'phone']
            ]
        ],
        'profile' => [
            'options' => [
                'access' => [
                    'groups' => [
                        3
                    ],
                    'self' => true
                ]
            ],
            'map' => [
                ['first_name', 'middle_name', 'last_name'],
                ['country', 'city'],
                ['sex', 'photo_file_id'],
                ['bday', 'phone']
            ]
        ]
    ];
    public static $labels = [
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'middle_name' => 'Отчество',
        'sex' => 'Пол',
        'photo_file_id' => 'Фото',
        'bday' => 'Дата рождения',
        'phone' => 'Мобильный телефон',
        'country' => 'Страна',
        'city' => 'Город',
        'user_id' => 'Пользователь'
    ];

    public static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'photo' => [
                'model' => 'Files\File',
                'col' => 'photo_file_id'
            ]
        ];
    }

    function name()
    {
        if ($this->first_name . $this->last_name . $this->middle_name) {
            return trim($this->first_name . ' ' . $this->last_name . ' ' . $this->middle_name);
        } else {
            return $this->user_id;
        }
    }

}
