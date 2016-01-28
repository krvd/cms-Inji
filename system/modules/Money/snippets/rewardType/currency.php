<?php

return [
    'name' => 'Валюта',
    'params' => [
        'currency_id' => [
            'type' => 'select'
        ],
        'type' => [
            'type' => 'select', 'source' => 'array', 'sourceArray' => [
                'procent' => 'Процент',
                'amount' => 'Кол-во'
            ]
        ],
        'amount' => [
            'type' => 'decimal'
        ]
    ],
    'viewer' => function($level) {
$levelTypes = [
    'procent' => 'Процент',
    'amount' => 'Сумма',
];
return $levelTypes[$level->params['type']->value] . ': ' . $level->params['amount']->value . ' ' . ($level->params['currency_id']->value ? \Money\Currency::get($level->params['currency_id']->value)->acronym() : '');
},
    'rewarder' => function($reward, $sums, $user, $rootUser, $level, $rewardGet) {
$wallets = $this->getUserWallets($user->id);
if (!empty($wallets[$level->params['currency_id']->value])) {
    $amount = 0;
    switch ($level->params['type']->value) {
        case 'procent':
            $finalSum = 0;
            foreach ($sums as $currency_id => $sum) {
                if ($currency_id != $level->params['currency_id']->value) {
                    $rate = \Money\Currency\ExchangeRate::get([
                                ['currency_id', $currency_id],
                                ['target_currency_id', $level->params['currency_id']->value],
                    ]);
                    if ($rate) {
                        $finalSum += $sum * $rate->rate;
                    }
                } else {
                    $finalSum += $sum;
                }
            }
            switch ($reward->round_type) {
                case 'round':
                    $finalSum = round($finalSum, $reward->round_precision);
                    $amount = $finalSum / 100 * $level->params['amount']->value;
                    break;
                case 'floor':
                    $finalSum = floor($finalSum);
                    $amount = $finalSum / 100 * $level->params['amount']->value;
                    break;
                default:
                    $amount = $finalSum / 100 * $level->params['amount']->value;
            }
            break;
        case 'amount':
            $amount = $level->params['amount']->value;
    }
    if (!$amount) {
        return 0;
    }
    if (!$rewardGet && $reward->block) {
        $block = new \Money\Wallet\Block();
        $block->wallet_id = $wallets[$level->params['currency_id']->value]->id;
        $block->amount = $amount;
        $block->comment = 'Партнерское вознаграждение от ' . $rootUser->name();
        $block->data = 'reward:' . $reward->id;
        $dateGenerators = $this->getSnippets('expiredDateGenerator');
        if ($reward->block_date_expired && !empty($dateGenerators[$reward->block_date_expired])) {
            $date = $dateGenerators[$reward->block_date_expired]($reward, $user);
            if (!empty($date['date'])) {
                $block->date_expired = $date['date'];
            }
            if (!empty($date['type'])) {
                $block->expired_type = $date['type'];
            }
        }
        $block->save();
    } else {
        $text = 'Вознаграждение по программе "' . $reward->name . '"';
        if ($rootUser->id != $user->id) {
            $text .= ' от ' . $rootUser->name();
        }
        $wallets[$level->params['currency_id']->value]->diff($amount, $text);
    }
}
return $amount;
}
];
