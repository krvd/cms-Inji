<?php

namespace Users;

class User extends \Model {

    static $cur;
    public static $objectName = "Пользователь";
    static $labels = [
        'mail' => 'E-Mail',
        'group_id' => 'Группа пользователя',
        'role_id' => 'Роль пользователя',
        'parent_id' => 'Спонсор',
    ];
    static $cols = [
        'mail' => ['type' => 'email'],
        'login' => ['type' => 'text'],
        'group_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'group', 'showCol' => 'group_name'],
        'role_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'role', 'showCol' => 'role_name'],
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
                'mail',
                'group_id',
                'role_id',
            ],
            'searchableCols' => ['mail']
        ],
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
                ['login', 'mail',],
                ['group_id', 'role_id'],
                ['form:info:manager']
            ]
        ]
    ];

    static function relations() {
        return [
            'group' => [
                'model' => 'Users\Group',
                'col' => 'group_id'
            ],
            'role' => [
                'model' => 'Users\Role',
                'col' => 'role_id'
            ],
            'info'=>[
                'type'=>'one',
                'model'=>'Users\Info',
                'col'=>'user_id'
            ]
        ];
    }
    function name(){
        return $this->info->name();
    }

    function isAdmin() {
        if ($this->group_id == 3) {
            return true;
        }
        return false;
    }

}
