<?php

namespace Users;

class User extends \Model {

    static $cur;
    static $labels = [
        'user_name' => 'ФИО',
        'user_mail' => 'E-Mail',
        'user_phone' => 'Телефон',
        'user_city' => 'Город',
        'user_group_id' => 'Группа пользователя',
        'user_role_id' => 'Роль пользователя',
        'user_parent_id' => 'Спонсор',
        'user_photo_file_id' => 'Фото'
    ];
    static $cols = [
        'user_name' => ['type' => 'text'],
        'user_mail' => ['type' => 'email'],
        'user_phone' => ['type' => 'text'],
        'user_city' => ['type' => 'text'],
        'user_group_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'group', 'showCol' => 'group_name'],
        'user_role_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'role', 'showCol' => 'role_name'],
        'user_photo_file_id' => ['type' => 'image', 'relation' => 'photo', 'showCol' => 'file_path'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'user_name',
                'user_mail',
                'user_phone',
                'user_city',
                'user_group_id',
                'user_role_id',
            ],
            'searchableCols' => ['user_name', 'user_mail', 'user_city', 'user_phone']
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['user_name', 'user_mail',],
                ['user_photo_file_id'],
                ['user_phone', 'user_city'],
                ['user_group_id', 'user_role_id']
            ]
        ],
        'profile' => [
            'map' => [
                ['user_name', 'user_photo_file_id'],
                ['user_phone', 'user_city'],
            ]
        ]
    ];

    static function relations() {
        return [
            'group' => [
                'model' => 'Users\Group',
                'col' => 'user_group_id'
            ],
            'role' => [
                'model' => 'Users\Role',
                'col' => 'user_role_id'
            ],
            'photo' => [
                'model' => 'Files\File',
                'col' => 'user_photo_file_id'
            ]
        ];
    }

    function isAdmin() {
        if ($this->group_id == 3) {
            return true;
        }
        return false;
    }

}
