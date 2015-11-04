<?php

namespace Callbacks;

class Callback extends \Model
{
    public static $objectName = "Отзыв";
    public static $cols = [
        'name' => ['type' => 'text'],
        'profession' => ['type' => 'text'],
        'view' => ['type' => 'bool'],
        'phone' => ['type' => 'text'],
        'mail' => ['type' => 'text'],
        'image_file_id' => ['type' => 'image'],
        'callback_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'text' => ['type' => 'html'],
        'date_create' => ['type' => 'dateTime',]
    ];
    public static $labels = [
        'name' => 'Имя',
        'profession' => 'Профессия',
        'view' => 'Отображается',
        'image_file_id' => 'Фото',
        'mail' => 'E-mail',
        'callback_type_id' => 'Тип отзыва',
        'text' => 'Текст',
        'user_id' => 'Пользователь',
        'phone' => 'Телефон',
        'date_create' => 'Дата создания'
    ];

    public static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'type' => [
                'model' => 'Callbacks\Callback\Type',
                'col' => 'callback_type_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'profession', 'user_id', 'view', 'date_create'
            ],
            'filters' => [
                'name', 'profession', 'phone', 'text', 'view', 'user_id', 'date_create'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'phone'],
                ['profession', 'image_file_id'],
                ['callback_type_id', 'view'],
                ['mail', 'user_id'],
                ['text'],
            ]
        ]
    ];

}
