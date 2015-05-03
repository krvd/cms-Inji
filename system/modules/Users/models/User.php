<?php
namespace Users;
class User extends \Model {

    static $labels = [
        'user_name' => 'ФИО',
        'user_mail' => 'E-Mail',
        'user_phone' => 'Телефон',
        'user_city' => 'Город',
        'user_group_id' => 'Группа пользователя',
        'user_role_id' => 'Роль пользователя',
        'user_parent_id' => 'Спонсор'
    ];
    static $dataTable = [
        'cols' => [
            'user_name' => [],
            'user_mail' => [],
            'user_phone' => [],
            'user_city' => [],
            'user_group_id' => ['relation' => 'group', 'showCol' => 'group_name'],
            'user_role_id' => ['relation' => 'role', 'showCol' => 'role_name'],
        ],
        'searchableCols' => ['user_name', 'user_mail', 'user_city', 'user_phone']
    ];
    static $forms = [
        'manage' => [
            'options' => [
                'user_name' => 'text',
                'user_parent_id' => ['relation' => 'parent', 'showCol' => 'user_name'],
            ],
            'map'=>[
                ['user_name','user_parent_id']
            ]
        ]
    ];

    static function table() {
        return 'users';
    }

    static function index() {
        return 'user_id';
    }

    static function relations() {
        return [
            'group' => [
                'model' => 'Group',
                'col' => 'user_group_id'
            ],
            'role' => [
                'model' => 'Role',
                'col' => 'user_role_id'
            ],
            'parent' => [
                'model' => 'User',
                'col' => 'user_parent_id'
            ]
        ];
    }

    function isAdmin() {
        if ($this->user_group_id == 3 || $this->user_group_id == 4) {
            return true;
        }
        return false;
    }

}
