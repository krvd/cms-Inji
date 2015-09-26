<?php

namespace Users;

class Group extends \Model
{
    static $objectName = 'Группа пользователей';
    static $labels = [
        'name' => 'Название',
        'user' => 'Пользователи',
        'role' => 'Роли'
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'user' => ['type' => 'select', 'source' => 'relation', 'relation' => 'users'],
        'role' => ['type' => 'select', 'source' => 'relation', 'relation' => 'roles'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Группы пользователей',
            'cols' => [
                'name', 'role', 'user'
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

    static function relations()
    {
        return [
            'roles' => [
                'type' => 'many',
                'model' => 'Users\Role',
                'col' => 'group_id'
            ],
            'users' => [
                'type' => 'many',
                'model' => 'Users\User',
                'col' => 'group_id'
            ]
        ];
    }

}
