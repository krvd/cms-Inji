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
        'mail' => 'E-Mail',
        'group_id' => 'Группа пользователя',
        'role_id' => 'Роль пользователя',
        'parent_id' => 'Пригласивший',
        'reg_date' => 'Дата регистрации',
        'blocked' => 'Заблокирован',
        'pass' => 'Пароль',
    ];
    static $cols = [
        'mail' => ['type' => 'email'],
        'pass' => ['type' => 'password'],
        'login' => ['type' => 'text'],
        'group_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'group'],
        'role_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'role'],
        'parent_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'parent'],
        'pass' => ['type' => 'changePassword'],
        'blocked' => [
            'type' => 'bool',
        ],
        'reg_date' => [
            'type' => 'dateTime',
        ]
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
                'reg_date'
            ],
            'sortable' => [
                'mail',
                'info:first_name',
                'info:last_name',
                'group_id',
                'role_id',
                'reg_date'
            ],
            'filters' => [
                'mail',
                'info:first_name',
                'info:last_name',
                'role_id',
                'reg_date'
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
            ],
            'map' => [
                ['login', 'mail',],
                ['group_id', 'role_id'],
                ['parent_id', 'blocked'],
                ['pass'],
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
                'model' => 'Users\Info',
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
