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
        'date_create' => 'Дата создания',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'code' => ['type' => 'text'],
        'refill' => ['type' => 'bool'],
        'wallet' => ['type' => 'bool'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Валюты',
            'cols' => ['name', 'code', 'wallet', 'refill', 'date_create']
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'code'],
                ['refill', 'wallet'],
            ]
        ]
    ];

    function acronym()
    {
        return "<acronym title='{$this->name()}'>{$this->code}</acronym>";
    }

}
