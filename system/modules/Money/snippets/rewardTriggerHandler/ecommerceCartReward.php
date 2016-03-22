<?php

return [
    'name' => 'Вознаграждение партнерам за закрытие корзины товаров в онлайн магазине',
    'handler' => function($cart, $trigger) {
        $sums = [];
        $rewardItemExist = empty($trigger->params['item_type_id']);

        if (!$rewardItemExist) {
            $allowTypes = explode(',', $trigger->params['item_type_id']->value);
        } else {
            $allowTypes = [];
        }
        foreach ($cart->cartItems as $cartItem) {
            if ($allowTypes && !in_array($cartItem->item->item_type_id, $allowTypes)) {
                continue;
            }
            $rewardItemExist = true;
            $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
            if (empty($sums[$currency_id])) {
                $sums[$currency_id] = $cartItem->price->price * $cartItem->count;
            } else {
                $sums[$currency_id] += $cartItem->price->price * $cartItem->count;
            }
        }

        if ($rewardItemExist) {
            App::$cur->money->reward($trigger->reward_id, $sums, $cart->user);
        }
    }];
        