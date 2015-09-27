<?php

namespace Users\User;

class Invite extends \Model
{
    public static $objectName = "Код пришлашения";
    static $labels = [
        'code' => 'Код',
        'type' => 'Тип',
        'user_id' => 'Пользователь',
        'limit' => 'Лимит приглашений',
        'count' => 'Использовано',
        'date_create' => 'Дата',
    ];
    static $cols = [
        'code' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'limit' => ['type' => 'number'],
        'count' => ['type' => 'number'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'code',
                'type',
                'user_id',
                'limit',
                'count',
                'date_create'
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['code', 'type',],
                ['user_id'],
                ['limit', 'count'],
            ]
        ]
    ];

    static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
        ];
    }

    function name()
    {
        return $this->code;
    }

}
