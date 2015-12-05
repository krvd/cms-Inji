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
