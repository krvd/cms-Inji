<?php

return [
    'name' => 'Прямой платеж',
    'handler' => function($cart) {
        $extra = new Ecommerce\Cart\Extra();
        $extra->name = 'Наценка для идентификации платежа';
        $extra->cart_id = $cart->id;
        $extra->price = '0.' . (strlen((string) $cart->id) > 1 ? substr((string) $cart->id, -2) : $cart->id);
        $extra->count = 1;
        $extra->currency_id = 3;
        $extra->save();
        return [
            '/ecommerce/cart/primary'
        ];
    }
        ];
        