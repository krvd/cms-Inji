<?php

/**
 * User
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Users;

class User extends \Model
{
    static $cur;
    public static $objectName = "Пользователь";
    static $labels = [
        'login' => 'Логин',
        'mail' => 'E-Mail',
        'pass' => 'Пароль',
        'parent_id' => 'Пригласивший',
        'group_id' => 'Группа',
        'role_id' => 'Роль',
        'admin_text' => 'Комментарий администратора',
        'activation' => 'Код активации',
        'blocked' => 'Заблокирован',
        'date_last_active' => 'Последняя активность',
        'date_create' => 'Дата регистрации',
    ];
    static $cols = [
        'login' => ['type' => 'text'],
        'mail' => ['type' => 'email'],
        'pass' => ['type' => 'password'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent'],
        'group_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'group'],
        'role_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'role'],
        'admin_text' => ['type' => 'html'],
        'activation' => ['type' => 'text'],
        'blocked' => ['type' => 'bool',],
        'date_last_active' => ['type' => 'dateTime'],
        'date_create' => ['type' => 'dateTime']
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
                'info:first_name',
                'info:last_name',
                'group_id',
                'role_id',
                'date_last_active',
                'date_create',
            ],
            'sortable' => [
                'mail',
                'info:first_name',
                'info:last_name',
                'group_id',
                'role_id',
                'date_last_active',
                'date_create'
            ],
            'filters' => [
                'mail',
                'info:first_name',
                'info:last_name',
                'role_id',
                'date_last_active',
                'date_create',
            ],
            'searchableCols' => ['mail']
        ],
    ];
    static $forms = [
        'manager' => [
            'inputs' => [
                'userSearch' => [
                    'type' => 'search',
                    'source' => 'relation',
                    'relation' => 'parent',
                    'label' => 'Пригласивший',
                    'cols' => [
                        'info:first_name',
                        'info:last_name',
                        'info:middle_name',
                        'mail'
                    ],
                    'col' => 'parent_id',
                ],
                'pass' => [
                    'type' => 'changePassword'
                ]
            ],
            'map' => [
                ['login', 'mail',],
                ['group_id', 'role_id'],
                ['userSearch', 'blocked'],
                ['pass'],
                ['activation'],
                ['admin_text'],
                ['form:info:manager']
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
                ['pass'],
                ['form:info:manager']
            ]
        ]
    ];

    static function relations()
    {
        return [
            'group' => [
                'model' => 'Users\Group',
                'col' => 'group_id'
            ],
            'role' => [
                'model' => 'Users\Role',
                'col' => 'role_id'
            ],
            'info' => [
                'type' => 'one',
                'model' => 'Users\User\Info',
                'col' => 'user_id'
            ],
            'socials' => [
                'type' => 'many',
                'model' => 'Users\User\Social',
                'col' => 'user_id'
            ],
            'parent' => [
                'model' => 'Users\User',
                'col' => 'parent_id'
            ],
            'users' => [
                'type' => 'many',
                'model' => 'Users\User',
                'col' => 'parent_id'
            ]
        ];
    }

    function name()
    {
        if ($this->info) {
            return trim($this->info->name());
        } else {
            $this->id;
        }
    }

    function isAdmin()
    {
        if ($this->group_id == 3) {
            return true;
        }
        return false;
    }

    function beforeDelete()
    {
        if ($this->info) {
            $this->info->delete();
        }
        $sessions = Session::getList(['where' => ['user_id', $this->id]]);
        foreach ($sessions as $session) {
            $session->delete();
        }
        foreach ($this->socials as $social) {
            $social->delete();
        }
    }

}
