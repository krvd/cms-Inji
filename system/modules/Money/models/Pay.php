<?php

namespace Money;

class Pay extends \Model
{
    static function relations()
    {
        return [
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
            'status' => [
                'model' => 'Money\Pay\Status',
                'col' => 'pay_status_id'
            ],
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

    static $cols = [
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
        'pay_status_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'status'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'sum' => ['type' => 'decimal'],
        'type' => ['type' => 'text'],
        'callback_module' => ['type' => 'text'],
        'callback_method' => ['type' => 'text'],
    ];
    static $labels = [
        'currency_id' => 'Валюта',
        'pay_status_id' => 'Статус',
        'sum' => 'Сумма',
        'user_id' => 'Пользователь',
        'type' => 'Тип',
        'callback_module' => 'Модуль обработчика',
        'callback_method' => 'Метод обработчика'
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Счета оплаты',
            'cols' => [
                'sum',
                'currency_id',
                'pay_status_id',
                'date_create'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'name' => 'Счет оплаты',
            'map' => [
                ['sum', 'currency_id'],
                ['user_id'],
                ['pay_status_id', 'type'],
                ['callback_module', 'callback_method']
            ]
        ]
    ];

}
