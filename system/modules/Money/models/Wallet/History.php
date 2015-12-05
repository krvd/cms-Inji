<?php

/**
 * Wallet history
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Wallet;

class History extends \Model
{
    static $cols = [
        'wallet_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'wallet'],
        'new' => ['type' => 'decimal'],
        'old' => ['type' => 'decimal'],
        'amount' => ['type' => 'decimal'],
        'comment' => ['type' => 'text'],
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
