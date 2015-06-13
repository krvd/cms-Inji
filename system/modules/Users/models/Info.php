<?php 

namespace Users;

class Info extends \Model {
    public static $objectName = "Расширеная информация пользователя";
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
            'type' => 'number'
        ],
        'photo' => [
            'type' => 'image'
        ],
        'bday' => [
            'type' => 'date'
        ],
        'phone' => [
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
    public static $labels = [
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'middle_name' => 'Отчество',
        'sex' => 'Пол',
        'photo' => 'Фото',
        'bday' => 'Дата рождения',
        'phone' => 'Мобильный телефон',
        'city' => 'Город',
        'user_id' => 'Пользователь'
    ];
    public static function relations() {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }
}
