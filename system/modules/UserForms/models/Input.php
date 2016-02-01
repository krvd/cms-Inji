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
    public static $objectName = 'Поле формы';
    public static $labels = [
        'label' => 'Название',
        'type' => 'Тип',
        'required' => 'Обязательное',
        'params' => 'Параметры'
    ];
    public static $cols = [
        'label' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'required' => ['type' => 'bool'],
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'label',
                'type',
                'required',
            ]
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['label'],
                ['type','required'],
            ]
        ]
    ];

}
