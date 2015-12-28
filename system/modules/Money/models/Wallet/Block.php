<?php

/**
 * Wallet block
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Wallet;

class Block extends \Model
{
    static $cols = [
        'wallet_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'wallet'],
        'amount' => ['type' => 'decimal'],
        'data' => ['type' => 'text'],
        'comment' => ['type' => 'text'],
        'expired_type' => ['type' => 'text'],
        'date_expired' => ['type' => 'dateTime'],
        'date_create' => ['type' => 'dateTime'],
    ];

    static function relations()
    {
        return [
            'wallet' => [
                'model' => 'Money\Wallet',
                'col' => 'wallet_id'
            ]
        ];
    }

}
