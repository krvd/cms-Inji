<?php

/**
 * Description of FormRecive
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace UserForms;

class Recive extends \Model
{
    static $objectName = 'Полученная форма';
    static $labels = [
        'form_title' => 'Название',
        'form_user_id' => 'Пользователь',
        'inputs' => 'Поля формы',
        'form_date_create' => 'Дата'
    ];
    static $cols = [
        'form_id' => ['relation' => 'form', 'showCol' => 'form_title'],
        'data' => [
            'type' => 'json',
            'view' => [
                'type' => 'moduleMethod',
                'module' => 'UserForms',
                'method' => 'formData'
            ],
        ],
        'date_create' => [],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Полученные формы',
            'cols' => [
                'form_id',
                'data',
                'date_create',
            ]
        ]
    ];

    static function relations()
    {
        return [
            'form' => [
                'model' => '\userForms\Form',
                'col' => 'form_id'
            ],
        ];
    }

}
