<?php

$currencies = Money\Currency::getList(['where' => ['wallet', 1]]);
if (!$currencies) {
    return false;
}
return [
    'name' => 'Мои кошельки',
    'fullWidget' => 'Money\cabinet/wallets',
    'smallWidget' => 'Money\cabinet/walletsWidget'
];
