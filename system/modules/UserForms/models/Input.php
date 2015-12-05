<?php

/**
 * Input
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
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
