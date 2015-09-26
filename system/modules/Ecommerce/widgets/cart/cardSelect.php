<?php

$userCards = \Ecommerce\Card\Item::getList(['where' => ['user_id', \Users\User::$cur->id]]);
foreach ($userCards as $userCard) {
    $checked = $cart->card_item_id == $userCard->id;
    $form->input('radio', "discounts[card_item_id]", $userCard->card->name, ['value' => $userCard->id, 'checked' => $checked, 'helpText' => $userCard->level->name . ' (' . $userCard->level->discount->name . ')<br />Сумма накоплений: ' . $userCard->sum . ' руб.']);
}
if (!$userCards) {
    echo '<p>У вас нет <b>дисконтной карты</b>, вы можете её оформить и сразу начать пльзоваться её приемуществами.</p>';
    echo '<p><a class ="btn btn-primary btn-xs" href = "/ecommerce/buyCard"><b>Оформить дисконтную карту</b></a></p>';
} else {
    echo '<p><button type="button" onclick="inji.Ecommerce.Cart.calcSum();" class ="btn btn-primary btn-xs"><b>Применить дисконтную карту</b></button></p>';
}
?>