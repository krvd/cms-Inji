<?php

/**
 * Transfer
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money;

class Transfer extends \Model
{
    static $cols = [
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'to_user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'toUser'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
        'amount' => ['type' => 'decimal'],
        'code' => ['type' => 'text'],
        'complete' => ['type' => 'bool'],
        'canceled' => ['type' => 'bool'],
    ];
    static $labels = [
        'amount' => 'Сумма'
    ];

    static function itemName($item)
    {
        return $item->pk() . '. ' . $item->name();
    }

    static $forms = [
        'transfer' => [
            'name' => 'Перевод средств',
            'successText' => 'Операция перевода средств была успешно начата',
            'inputs' => [
                'userSearch' => [
                    'type' => 'search',
                    'source' => 'relation',
                    'relation' => 'toUser',
                    'showCol' => [
                        'type' => 'staticMethod',
                        'class' => 'Money\Transfer',
                        'method' => 'itemName',
                    ],
                    'label' => 'Получатель',
                    'cols' => [
                        'info:first_name',
                        'info:last_name',
                        'info:middle_name',
                        'mail'
                    ],
                    'col' => 'to_user_id',
                    'required' => true,
                    'validator' => 'userSearch'
                ],
                'wallets' => [
                    'type' => 'select',
                    'source' => 'method',
                    'module' => 'Money',
                    'method' => 'getUserWallets',
                    'params' => [null, false, true],
                    'label' => 'Кошелек',
                    'col' => 'currency_id',
                    'required' => true
                ],
                'amount' => [
                    'type' => 'number',
                    'validator' => 'amount',
                    'required' => true
                ],
            ],
            'map' => [
                ['userSearch'],
                ['wallets', 'amount']
            ]
        ]
    ];

    static function validators()
    {
        return [
            'userSearch' => function($activeForm, $request) {
                if (empty($request['userSearch'])) {
                    throw new \Exception('Не указан получатель');
                }
                if (!((int) $request['userSearch'])) {
                    throw new \Exception('Не указан получатель');
                }
                $user = \Users\User::get((int) $request['userSearch']);
                if (!$user) {
                    throw new \Exception('Такой пользователь не найден');
                }
                if ($user->id == \Users\User::$cur->id) {
                    throw new \Exception('Нельзя выбрать себя в качестве получателя');
                }
                return true;
            },
            'amount' => function($activeForm, $request) {
                if (empty($request['amount'])) {
                    throw new \Exception('Не указана сумма');
                }
                if (!((float) $request['amount'])) {
                    throw new \Exception('Не указана сумма');
                }
                $amount = (float) $request['amount'];
                if (empty($request['wallets'])) {
                    throw new \Exception('Не указан кошелек');
                }
                if (!((int) $request['wallets'])) {
                    throw new \Exception('Не указан кошелек');
                }
                $wallets = \App::$cur->money->getUserWallets();
                if (empty($wallets[(int) $request['wallets']])) {
                    throw new \Exception('У вас нет такого кошелька');
                }
                $wallet = $wallets[(int) $request['wallets']];
                if ($wallet->amount < $amount) {
                    throw new \Exception('У вас недостаточно средств на кошельке');
                }
                return true;
            }
        ];
    }

    static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'toUser' => [
                'model' => 'Users\User',
                'col' => 'to_user_id'
            ],
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ]
        ];
    }

    function name()
    {
        return 'Перевод на сумму ' . $this->amount . ' ' . $this->currency->name . ' от ' . $this->user->name() . ' для ' . $this->toUser->name();
    }

}
