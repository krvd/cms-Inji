<?php

/**
 * Money
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Money extends Module
{
    public $currentMerchant = '';

    function init()
    {
        if (!empty($this->config['defaultMerchant'])) {
            $this->currentMerchant = $this->config['defaultMerchant'];
        }
    }

    function refillPayRecive($data)
    {
        $wallets = $this->getUserWallets($data['pay']->user_id);
        foreach ($wallets as $wallet) {
            if ($wallet->currency_id == $data['pay']->currency_id) {
                $wallet->amount += $data['pay']->sum;
                $wallet->save();
                break;
            }
        }
    }

    function goToMerchant($pay, $merchant, $method, $merchantOptions)
    {
        $objectName = $merchant->object_name;

        if (is_array($pay)) {
            $pay = new Money\Pay($pay);
            $pay->save();
        }
        switch ($method['type']) {
            case 'transfer':
                $sum = $pay->sum / $method['transfer']->rate;
                break;
            default:
                $sum = $pay->sum;
        }
        $className = 'Money\MerchantHelper\\' . $objectName;
        return $className::goToMerchant($pay->id, $sum, $method['currency'], $merchantOptions['description'], $merchantOptions['success'], $merchantOptions['false']);
    }

    function reciver($data, $system, $status, $mr)
    {
        if ($system) {
            $merchant = \Money\Merchant::get($system, 'object_name');
        } else {
            $merchant = false;
        }
        if ($merchant) {
            $this->currentMerchant = $system;
        }
        $className = 'Money\MerchantHelper\\' . $this->currentMerchant;
        $result = $className::reciver($data, $status);
        $result['pay'] = null;
        if (!empty($result['payId'])) {
            $result['pay'] = Money\Pay::get($result['payId']);
            $mr->pay_id = $result['payId'];
        }
        if ($result['pay'] && $result['pay']->pay_status_id == 1) {
            $statuses = \Money\Pay\Status::getList(['key' => 'code']);
            if (!empty($statuses[$result['status']])) {
                $result['pay']->pay_status_id = $statuses[$result['status']]->id;
            }
            $result['pay']->date_recive = date('Y-m-d H:i:s');
            $result['pay']->save();
            if ($result['status'] == 'success' && $result['pay']->callback_module && $result['pay']->callback_method) {
                App::$cur->{$result['pay']->callback_module}->{$result['pay']->callback_method}($result);
            }
        }
        if (!empty($result['callback'])) {
            echo $result['callback'];
            $mr->result_callback = $result['callback'];
        }
        if (!empty($result['status'])) {
            $mr->status = $result['status'];
        }
        $mr->save();
    }

    function getUserWallets($userId = null)
    {
        $userId = $userId ? $userId : \Users\User::$cur->id;
        if (!$userId) {
            return [];
        }
        $currencies = Money\Currency::getList(['where' => ['wallet', 1]]);
        $wallets = Money\Wallet::getList(['where' => ['user_id', $userId], 'key' => 'currency_id']);
        $result = [];
        foreach ($currencies as $currency) {
            if (empty($wallets[$currency->id])) {
                $wallet = new Money\Wallet();
                $wallet->user_id = $userId;
                $wallet->currency_id = $currency->id;
                $wallet->save();
                $result[$currency->id] = $wallet;
            } else {
                $result[$currency->id] = $wallets[$currency->id];
            }
        }
        return $result;
    }

    function rewardTrigger($event)
    {
        $item = Money\Reward\Condition\Item::get([['type', 'event'], ['value', $event['eventName']]]);

        if ($item) {
            $sums = [];
            foreach ($event['eventObject']->cartItems as $cartItem) {
                $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
                if (empty($sums[$currency_id])) {
                    $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
                } else {
                    $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
                }
            }
            $this->reward($item->condition->reward_id, $sums, $event['eventObject']->user);
        }
    }

    function reward($reward_id, $sums, $rootUser = null)
    {
        $reward = \Money\Reward::get($reward_id);
        foreach ($reward->levels(['order' => ['level', 'asc']]) as $level) {
            $user = $rootUser ? $rootUser : \Users\User::$cur;
            for ($i = 0; $i < $level->level; $i++) {
                $next = $user && $user->parent ? $user->parent : false;
                if (!$next && $reward->lasthaveall) {
                    break;
                }
                $user = $next;
            }
            if (!$user) {
                break;
            }
            $wallets = $this->getUserWallets($user->id);
            if (!empty($wallets[$level->currency_id])) {
                switch ($level->type) {
                    case 'procent':
                        $finalSum = 0;
                        foreach ($sums as $currency_id => $sum) {
                            if ($currency_id != $level->currency_id) {
                                $rate = \Money\Currency\ExchangeRate::get([
                                            ['currency_id', $currency_id],
                                            ['target_currency_id', $level->currency_id],
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
                                $amount = round($finalSum / 100 * $level->amount, $reward->round_precision);
                                break;
                            case 'floor':
                                $finalSum = floor($finalSum);
                                $amount = floor($finalSum / 100 * $level->amount);
                                break;
                            default:
                                $amount = $finalSum / 100 * $level->amount;
                        }


                        $wallets[$level->currency_id]->amount += $amount;
                        break;
                    case 'amount':
                        $wallets[$level->currency_id]->amount += $level->amount;
                }
                $wallets[$level->currency_id]->save();
            }
        }
    }

}
