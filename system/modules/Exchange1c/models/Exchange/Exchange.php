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

namespace Exchange1c;

class Exchange extends \Model {

    static $cols = [
        'type' => ['type' => 'text'],
        'session' => ['type' => 'text'],
        'path' => ['type' => 'text'],
        'date_create' => ['type' => 'dateTime'],
        'date_end' => ['type' => 'dateTime'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'type', 'session', 'path', 'date_create'
            ],
            'rowButtons' => [
                'open', ['href' => '/admin/exchange1c/reExchange', 'text' => '<i class = "glyphicon glyphicon-refresh"></i>'], 'edit', 'delete'
            ],
        ]
    ];

    static function relations() {
        return[
            'logs' => [
                'type' => 'many',
                'model' => 'Exchange1c\Exchange\Log',
                'col' => 'exchange_id'
            ],
            'files' => [
                'type' => 'many',
                'model' => 'Exchange1c\Exchange\File',
                'col' => 'exchange_id'
            ]
        ];
    }

}
