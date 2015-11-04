<?php

/**
 * Description of Callback
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace UserForms;

class Form extends \Model
{
    static $objectName = 'Форма обращения с сайта';
    static $labels = [
        'name' => 'Название',
        'description' => 'Описание',
        'user_id' => 'Пользователь',
        'inputs' => 'Поля формы',
        'date_create' => 'Дата'
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'description' => ['type' => 'html'],
        'user_id' => [ 'type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'inputs' => [ 'type' => 'dataManager', 'relation' => 'inputs'],
        'date_create' => ['type' => 'dateTime'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'user_id',
                'date_create',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'name' => 'Форма приема обращений с сайта',
            'map' => [
                ['name'],
                ['description'],
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
