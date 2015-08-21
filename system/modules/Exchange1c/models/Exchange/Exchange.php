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
            'cols'=>[
                'type','session','path','date_create','date_end'
            ]
        ]
    ];

}
