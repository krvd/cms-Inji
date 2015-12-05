<?php
/**
 * Merchant currency
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Money\Merchant;

class Currency extends \Model
{
    static $objectName = 'Валюта системы оплаты';
    static $cols = [
        'code' => ['type' => 'text'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
        'merchant_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'merchant'],
    ];
    static $labels = [
        'currency_id' => 'Валюта',
        'merchant_id' => 'Система оплаты',
        'code' => 'Код',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Валюты системы оплаты',
            'cols' => [
                'currency_id',
                'code',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['currency_id', 'code'],
                ['merchant_id'],
            ]
    ]];

    static function relations()
    {
        return [
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
            'merchant' => [
                'model' => 'Money\Merchant',
                'col' => 'merchant_id'
            ]
        ];
    }

}
