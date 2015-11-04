<?php

/**
 * Description of FormInput
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace UserForms;

class Input extends \Model
{
    static $objectName = 'Поле формы';
    static $labels = [
        'label' => 'Название',
        'type' => 'Тип',
        'required' => 'Обязательное',
        'params' => 'Параметры'
    ];
    static $cols = [
        'label' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'required' => ['type' => 'bool'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'label',
                'type',
                'required',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['label'],
                ['type','required'],
            ]
        ]
    ];

}
