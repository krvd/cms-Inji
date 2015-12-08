<?php

/**
 * Money module
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
                $wallet->diff($data['pay']->sum, 'Пополнение');
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

    function getUserBlocks($userId = null)
    {
        $userId = $userId ? $userId : \Users\User::$cur->id;
        $blocked = \Money\Wallet\Block::getList(['where' => ['wallet:user_id', $userId]]);
        $blocks = [];
        foreach ($blocked as $block) {
            if ($block->date_expired != '0000-00-00 00:00:00' && \DateTime::createFromFormat('Y-m-d H:i:s', $block->date_expired) <= new \DateTime()) {
                if ($block->expired_type == 'burn') {
                    $block->delete();
                }
                continue;
            }
            if (empty($blocks[$block->wallet->currency_id])) {
                $blocks[$block->wallet->currency_id] = $block->amount;
            } else {
                $blocks[$block->wallet->currency_id]+= $block->amount;
            }
        }
        return $blocks;
    }

    function getUserWallets($userId = null, $walletIdasKey = false, $forSelect = false)
    {
        $userId = $userId ? $userId : \Users\User::$cur->id;
        if (!$userId) {
            return [];
        }
        $this->getUserBlocks($userId);
        $currencies = Money\Currency::getList(['where' => ['wallet', 1]]);
        $wallets = Money\Wallet::getList(['where' => ['user_id', $userId], 'key' => 'currency_id']);
        $result = [];
        foreach ($currencies as $currency) {
            if (empty($wallets[$currency->id])) {
                $wallet = new Money\Wallet();
                $wallet->user_id = $userId;
                $wallet->currency_id = $currency->id;
                $wallet->save();
                $result[$walletIdasKey ? $wallet->id : $currency->id] = $forSelect ? $wallet->name() : $wallet;
            } else {
                $result[$walletIdasKey ? $wallets[$currency->id]->id : $currency->id] = $forSelect ? $wallets[$currency->id]->name() : $wallets[$currency->id];
            }
        }
        return $result;
    }

    function rewardTrigger($event)
    {
        $trigger = Money\Reward\Trigger::get([['type', 'event'], ['value', $event['eventName']]]);

        if ($trigger) {
            $handlers = $this->getSnippets('rewardTriggerHandler');
            if (!empty($handlers[$trigger->handler])) {
                $handlers[$trigger->handler]['handler']($event['eventObject'], $trigger);
            }
        }
    }

    function rewardConditionTrigger($event)
    {
        $item = Money\Reward\Condition\Item::get([['type', 'event'], ['value', $event['eventName']]]);
        if ($item) {
            $recivers = $this->getSnippets('rewardConditionItemReciver');
            if (!empty($recivers[$item->reciver])) {
                $recivers[$item->reciver]['reciver']($event['eventObject'], $item);
                if ($item->condition->reward->block) {
                    $item->condition->reward->checkBlocked();
                }
            }
        }
    }

    function reward($reward_id, $sums, $rootUser = null)
    {
        $rootUser = $rootUser ? $rootUser : \Users\User::$cur;
        $reward = \Money\Reward::get($reward_id);
        foreach ($reward->levels(['order' => ['level', 'asc']]) as $level) {
            $user = $rootUser;
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
            $rewardGet = true;
            foreach ($reward->conditions as $condition) {
                if (!$rewardGet) {
                    break;
                }
                foreach ($condition->items as $item) {
                    $count = 0;
                    foreach ($item->recives(['where' => ['user_id', $user->id]]) as $recive) {
                        $count += $recive->count;
                    }
                    if ($count < $item->count) {
                        $rewardGet = false;
                        break;
                    }
                }
            }
            if (!$rewardGet && !$reward->block) {
                continue;
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
                                $amount = $finalSum / 100 * $level->amount;
                                break;
                            case 'floor':
                                $finalSum = floor($finalSum);
                                $amount = $finalSum / 100 * $level->amount;
                                break;
                            default:
                                $amount = $finalSum / 100 * $level->amount;
                        }
                        break;
                    case 'amount':
                        $amount = $level->amount;
                }
                if (!$rewardGet && $reward->block) {
                    $block = new \Money\Wallet\Block();
                    $block->wallet_id = $wallets[$level->currency_id]->id;
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
                    $wallets[$level->currency_id]->diff($amount, 'Партнерское вознаграждение от ' . $rootUser->name());
                }
            }
        }
    }

}
