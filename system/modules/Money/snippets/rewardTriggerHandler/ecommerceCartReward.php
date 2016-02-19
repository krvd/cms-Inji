<?php

return [
    'name' => 'Вознаграждение партнерам за закрытие корзины товаров в онлайн магазине',
    'handler' => function($cart, $trigger) {
        $sums = [];
        $rewardItemExist = empty($trigger->params['item_type_id']);
        foreach ($cart->cartItems as $cartItem) {
            if (!empty($trigger->params['item_type_id']) && $trigger->params['item_type_id']->value != $cartItem->item->item_type_id) {
                continue;
            }
            $rewardItemExist = true;
            $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
            if (empty($sums[$currency_id])) {
                $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
            } else {
                $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
            }
        }
        var_dump($rewardItemExist, $trigger->_params, $sums);
        //exit();
        if ($rewardItemExist) {
            App::$cur->money->reward($trigger->reward_id, $sums, $cart->user);
        }
    }
        ];
        