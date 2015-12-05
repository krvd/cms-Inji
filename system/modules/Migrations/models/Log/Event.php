<?php

/**
 * Log event
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Log;

class Event extends \Model
{
    static $objectName = 'Событие истории миграции';
    static $labels = [
        'type' => 'Тип',
        'info' => 'Информация',
        'date_create' => 'Дата события'
    ];
    static $cols = [
        'type' => ['type' => 'text'],
        'info' => ['type' => 'text'],
        'date_create' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => ['type', 'info', 'date_create']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['type'],
                ['info'],
            ]
        ]
    ];

}
