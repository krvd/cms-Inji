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
        'params' => 'Параметры'
    ];
    static $cols = [
        'label' => ['type' => 'text'],
        'type' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'label',
                'type',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['label'],
                ['type'],
            ]
        ]
    ];

}
