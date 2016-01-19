<?php

/**
 * Currency
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money;

class Currency extends \Model
{
    static $objectName = 'Валюта';
    static $labels = [
        'name' => 'Название',
        'code' => 'Обозначение',
        'wallet' => 'Кошелек на сайте',
        'refill' => 'Пополнение',
        'transfer' => 'Переводы',
        'round_type' => 'Округдение при выводе',
        'round_precision' => 'Количество занков после запятов при округдлении',
        'date_create' => 'Дата создания',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'refill' => ['type' => 'bool'],
        'wallet' => ['type' => 'bool'],
        'transfer' => ['type' => 'bool'],
        'round_type' => ['type' => 'text'],
        'round_precision' => ['type' => 'number'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Валюты',
            'cols' => ['name', 'code', 'wallet', 'refill', 'transfer', 'date_create']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'code'],
                ['refill', 'wallet', 'transfer'],
            ]
        ]
    ];

    function acronym()
    {
        return "<acronym title='{$this->name()}'>{$this->code}</acronym>";
    }

    function beforeDelete()
    {
        if ($this->id) {
            $wallets = Wallet::getList(['where' => ['currency_id', $this->id]]);
            foreach ($wallets as $wallet) {
                $wallet->delete();
            }
        }
    }

}
