<?php

$cart = $item;
$sums = [];
$allSums = [];
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
foreach ($cart->extras as $extra) {
    $currency_id = $extra->currency ? $extra->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
    if (empty($sums[$currency_id])) {
        $sums[$currency_id] = $extra->price * $extra->count;
    } else {
        $sums[$currency_id] += $extra->price * $extra->count;
    }
}
if ($sums) {
    foreach ($sums as $currency_id => $sum) {
        if (!$sum) {
            continue;
        }
        echo number_format($sum, 2, '.', ' ');
        if (App::$cur->money) {
            $currency = Money\Currency::get($currency_id);
            if ($currency) {
                echo '&nbsp;' . $currency->acronym();
            } else {
                echo '&nbsp;р.';
            }
        } else {
            echo '&nbsp;р.';
        }
        echo '<br />';
    }
}