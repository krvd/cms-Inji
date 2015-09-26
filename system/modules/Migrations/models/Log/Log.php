<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations;

class Log extends \Model
{
    static $objectName = 'История миграции';
    static $labels = [
        'result' => 'Результат',
        'source' => 'Источник',
        'event' => 'События',
        'date_create' => 'Дата начала'
    ];
    static $cols = [
        'result' => ['type' => 'text'],
        'source' => ['type' => 'text'],
        'date_create' => ['type' => 'text'],
        'event' => ['type' => 'dataManager', 'relation' => 'events']
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => ['source', 'result', 'event', 'date_create']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['source'],
                ['result'],
                ['event'],
            ]
        ]
    ];

    static function relations()
    {
        return [
            'events' => [
                'type' => 'many',
                'model' => 'Migrations\Log\Event',
                'col' => 'log_id'
            ]
        ];
    }

}
