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

namespace Money;

class Wallet extends \Model
{
    static function relations()
    {
        return [
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
        ];
    }

    function beforeSave()
    {
        if ($this->pk()) {
            $cur = Wallet::get($this->pk());
            if ($cur->amount != $this->amount) {
                $history = new Wallet\History();
                $history->wallet_id = $this->pk();
                $history->old = $cur->amount;
                $history->new = $this->amount;
                $history->save();
            }
        }
    }

    function name()
    {
        return $this->currency->name();
    }

}
