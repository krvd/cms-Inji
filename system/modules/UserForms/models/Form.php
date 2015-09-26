<?php

/**
 * Description of Callback
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace UserForms;

class Form extends \Model
{
    static $labels = [
        'title' => 'Название',
        'user_id' => 'Пользователь',
        'inputs' => 'Поля формы',
        'date_create' => 'Дата'
    ];
    static $cols = [
        'title' => ['type' => 'text'],
        'user_id' => [ 'type' => 'select', 'source' => 'relation', 'relation' => 'user', 'showCol' => 'user_name'],
        'date_create' => ['type' => 'dateTime'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'title',
                'user_id',
                'date_create',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['title'],
                ['inputs'],
            ]
        ]
    ];

    static function relations()
    {
        return [
            'user' => [
                'model' => '\Users\User',
                'col' => 'user_id'
            ],
            'inputs' => [
                'type' => 'many',
                'model' => '\UserForms\Input',
                'col' => 'form_id',
            ],
        ];
    }

}
