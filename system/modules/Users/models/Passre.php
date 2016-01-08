<?php

/**
 * Passre
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Users;

class Passre extends \Model
{
    static $cols = [
        'hash' => ['type' => 'textarea'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'status' => ['type' => 'number'],
        'date_create' => ['type' => 'dateTime']
    ];

    static function relations()
    {
        return[
            'user' => [
                'col' => 'user_id',
                'model' => 'Users\User'
            ]
        ];
    }

}
