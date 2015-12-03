<?php

return [
    'name' => 'Онлайн оплата',
    'handler' => function($cart) {
        if (\App::$cur->money) {
            $sums = [];
            foreach ($cart->cartItems as $cartItem) {
                $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
                if (empty($sums[$currency_id])) {
                    $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
                } else {
                    $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
                }
            }
            if ($cart->delivery && $cart->delivery->price) {
                $currency_id = $cart->delivery->currency_id;
                if (empty($sums[$currency_id])) {
                    $sums[$currency_id] = $cart->delivery->price;
                } else {
                    $sums[$currency_id] += $cart->delivery->price;
                }
            }
            foreach ($sums as $currency_id => $sum) {
                if (!$currency_id) {
                    continue;
                }
                $pay = new Money\Pay([
                    'data' => $cart->id,
                    'currency_id' => $currency_id,
                    'user_id' => \Users\User::$cur->id,
                    'sum' => $sum,
                    'description' => 'Оплата заказа №' . $cart->id . ' в онлайн-магазине',
                    'type' => 'pay',
                    'pay_status_id' => 1,
                    'callback_module' => 'Ecommerce',
                    'callback_method' => 'cartPayRecive'
                ]);
                $pay->save();
            }
            return ['/money/merchants/pay/', 'Ваш заказ был создан. Вам необходимо оплатить счета, после чего с вами свяжется администратор для уточнения дополнительной информации'];
        }
    }
        ];
        