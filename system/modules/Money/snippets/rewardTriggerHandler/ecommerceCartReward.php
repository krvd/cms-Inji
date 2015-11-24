<?php

return [
    'name' => 'Вознаграждение партнерам за закрытие корзины товаров в онлайн магазине',
    'handler' => function($cart, $trigger) {
        $sums = [];
        foreach ($cart->cartItems as $cartItem) {
            $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
            if (empty($sums[$currency_id])) {
                $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
            } else {
                $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
            }
        }
        App::$cur->money->reward($trigger->reward_id, $sums, $cart->user);
    }
        ];
        