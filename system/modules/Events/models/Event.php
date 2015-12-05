<?php

/**
 * Event
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Events;

class Event extends \Model
{
    static $objectName = 'Событие системы';
    static $labels = [
        'name' => 'Название',
        'event' => 'Событие'
    ];
    static $storage = ['type' => 'moduleConfig'];
    static $cols = [
        'name' => ['type' => 'text'],
        'event' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'event'
            ],
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'event'],
            ]
        ]
    ];

    static function index()
    {
        return 'id';
    }

}
