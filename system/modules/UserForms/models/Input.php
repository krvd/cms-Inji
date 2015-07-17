<?php

/**
 * Description of FormInput
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */

namespace UserForms;

class Input extends \Model {

    static $labels = [
        'label' => 'Название',
        'fit_id' => 'Тип',
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
