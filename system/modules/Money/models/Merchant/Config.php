<?php

/**
 * Merchant config
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Merchant;

class Config extends \Model
{
    static $objectName = 'Опция системы оплаты';
    static $cols = [
        'name' => ['type' => 'text'],
        'value' => ['type' => 'textarea'],
    ];
    static $labels = [
        'name' => 'Название',
        'value' => 'Значение',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Опции системы оплаты',
            'cols' => [
                'name',
                'value',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['value'],
            ]
    ]];

    static function relations()
    {
        return [
            'merchant' => [
                'model' => 'Money\Merchant',
                'col' => 'merchant_id'
            ]
        ];
    }

}
