<div class="ecommerce">
    <div class="cart-order_page">
        <h2>Быстрое оформление заказа</h2>
        <?php
        if (!$cart || !$cart->cartItems)
            echo "<h1>Ваша корзина пуста</h1>";
        else {
            $cartDelivery = $cart->delivery;
            if (!$cartDelivery) {
                $cartDelivery = $deliverys[key($deliverys)];
            }
            $deliveryPrice = (($cart->sum >= $cartDelivery->max_cart_price) ? '0' : $cartDelivery->price);
            $cartPayType = $cart->payType;
            if (!$cartPayType) {
                $cartPayType = $payTypes[key($payTypes)];
            }
            ?>
            <form method = 'POST'>
                <div class="row">
                    <div class="col-sm-4">
                        <?php if (!Users\User::$cur->id) { ?>
                            <fieldset id="account">
                                <h4>Ваш аккаунт</h4>
                                <div class="form-group required">
                                    <label class="control-label">E-Mail</label>
                                    <input required type="text" name="user_mail" value="<?= (!empty($_POST['user_mail'])) ? $_POST['user_mail'] : (($cart->email) ? $cart->email : ''); ?>" placeholder="E-Mail" class="form-control"/>
                                </div>
                            </fieldset>
                        <?php } ?>
                        <fieldset id="address">
                            <h4>Контактная информация</h4>
                            <?php
                            foreach (Ecommerce\UserAdds\Field::getList() as $field) {
                                ?>
                                <div class="form-group required">
                                    <label class="control-label"><?= $field->name; ?></label>
                                    <input <?= $field->required ? 'required' : ''; ?> type="<?= $field->type; ?>" name="userAdds[fields][<?= $field->id; ?>]" value="<?= (!empty($_POST['userAdds']['fields'][$field->id])) ? $_POST['userAdds']['fields'][$field->id] : ''; ?>" placeholder="<?= $field->name; ?>" class="form-control"/>
                                </div>
                                <?php
                            }
                            ?>
                        </fieldset>                                
                    </div>

                    <div class="col-sm-8">
                        <div class ='row'>
                            <div class ='col-sm-6'>
                                <h3>Способ доставки</h3>
                                <?php
                                foreach ($deliverys as $delivery) {
                                    if ((!empty($_POST['delivery']) && $_POST['delivery'] == $delivery->id) || $delivery == $cartDelivery) {
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }
                                    ?>
                                    <div class="radio">
                                        <label>
                                            <input name="delivery" value="<?= $delivery->id; ?>" <?= $checked; ?> type="radio">
                                            <?= $delivery->name; ?> - <?= $delivery->price; ?> руб.
                                            <?php
                                            if ((float) $delivery->max_cart_price) {
                                                echo '<br/><small>При заказе товаров на сумму от ' . $delivery->max_cart_price . ' руб, доставка курьером - бесплатно</small>';
                                            }
                                            ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class ='col-sm-6'>
                                <h3>Способ оплаты</h3>
                                <?php
                                foreach ($payTypes as $payType) {
                                    if ((!empty($_POST['payType']) && $_POST['payType'] == $payType->id) || $payType == $cartPayType) {
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }
                                    ?>
                                    <div class="radio">
                                        <label>
                                            <input name="payType" value="<?= $payType->id; ?>" <?= $checked; ?> type="radio">
                                            <?= $payType->name; ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                                ?>            
                            </div>                        
                        </div>                        
                        <h2 class="secondary-title">Корзина товаров</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-left" colspan="2">Название товара</th>
                                        <th class="text-left" style="min-width: 225px" colspan="2">Количество</th>
                                        <th class="text-right">Цена</th>
                                        <th class="text-right">Итого</th>
                                    </tr>
                                </thead>
                                <tbody class="cartitems">
                                    <?php
                                    foreach ($cart->cartItems as $cartItem) {
                                        $this->widget('Ecommerce\cart/row', ['cartItem' => $cartItem]);
                                    }
                                    ?>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="cartsums">
                                        <td colspan="4" class="text-right">Сумма:</td>
                                        <td class="text-right"><?= $cart->sum; ?> руб.</td>
                                    </tr>
                                    <tr class="deliverysum">
                                        <td colspan="4" class="text-right"><?= $cartDelivery->name; ?>:</td>
                                        <td class="text-right"><?= $deliveryPrice; ?> руб.</td>
                                    </tr>
                                    <?php
                                    if ($packchecked) {
                                        $packSum = ceil(($cart->sum + $deliveryPrice) / 1000) * (float) $packItem->price->price;
                                    } else {
                                        $packSum = 0;
                                    }
                                    ?>
                                    <tr class="packssum hidden">
                                        <td colspan="4" class="text-right">Пакеты:</td>
                                        <td class="text-right"><?= $packSum ?> руб.</td>
                                    </tr>
                                    <tr class = 'allsums'>
                                        <td colspan="4" class="text-right">Итого:</td>
                                        <td class="text-right"><?= $cart->sum + $deliveryPrice + $packSum; ?> руб.</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="checkout-content confirm-section">
                            <h3>Вы можете добавить комментарий к своему заказу</h3>
                            <div class="form-group">    
                                <textarea name="comment" rows="5" class="form-control"><?= (!empty($_POST['comment'])) ? $_POST['comment'] : ''; ?></textarea>
                            </div>
                            <div class="confirm-order">
                                <button data-loading-text="Подождите.." class="btn btn-primary">Подтверждение заказа</button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            <?php
        }
        ?>
    </div>
</div>