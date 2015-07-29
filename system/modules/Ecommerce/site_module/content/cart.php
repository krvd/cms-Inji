<h1 class="heading-title">Быстрое оформление заказа</h1>
<?php
if (!$cart || !$cart->cartItems)
    echo "<h1>Ваша корзина пуста</h1>";
else {
    $i = 0;
    $summ = 0;
    foreach ($cart->cartItems as $cartItem) {

        if (!empty($cartItem->item->options['itemImage']) && $cartItem->item->options['itemImage']->file) {
            $path = $cartItem->item->options['itemImage']->file->file_path;
        } elseif (!empty($cartItem->item->options['image']) && $cartItem->item->options['image']->file) {
            $path = $cartItem->item->options['image']->file->file_path;
        } else {
            $path = '/static/images/no-image.png';
        }
        $summ += $cartItem->price->ciprice_price * $cartItem->cci_count;
        $asum = $summ + (($summ >= $deliverys[key($deliverys)]->cd_max_cart_price) ? '0' : $deliverys[key($deliverys)]->cd_price);
    }
    ?>
    <form method = 'POST'>
        <div class="journal-checkout">
            <div class="left">
                <div class="checkout-content checkout-register">
                    <fieldset id="account">
                        <h2 class="secondary-title">Личные данные</h2>
                        <div class="form-group required">
                            <label class="control-label">ФИО</label>
                            <input required type="text" name="user_name" value="<?= (!empty($_POST['user_name'])) ? $_POST['user_name'] : (($cart->cc_fio) ? $cart->cc_fio : ''); ?>" placeholder="ФИО" class="form-control"/>
                        </div>
                        <?php if (!$this->users->cur->user_id) { ?>
                            <div class="form-group required">
                                <label class="control-label">E-Mail</label>
                                <input required type="text" name="user_mail" value="<?= (!empty($_POST['user_mail'])) ? $_POST['user_mail'] : (($cart->cc_email) ? $cart->cc_email : ''); ?>" placeholder="E-Mail" class="form-control"/>
                            </div>
                        <?php } ?>
                        <div class="form-group required">
                            <label class="control-label">Телефон</label>
                            <input required type="text" name="user_phone" value="<?= (!empty($_POST['user_phone'])) ? $_POST['user_phone'] : (($cart->cc_tel) ? $cart->cc_tel : ''); ?>" placeholder="Телефон" class="form-control"/>
                        </div>
                    </fieldset>
                    <fieldset id="address">
                        <h2 class="secondary-title">Адрес</h2>
                        <div class=" checkout-payment-form">
                            <div class="form-horizontal form-payment">
                                <div id="payment-new" style="display: block;">
                                    <div class="form-group required">
                                        <label class="col-sm-2 control-label">Город</label>
                                        <input required type="text" name="city" value="<?= (!empty($_POST['city'])) ? $_POST['city'] : (($cart->cc_city) ? $cart->cc_city : ''); ?>" placeholder="Город" class="form-control"/>
                                    </div>
                                    <div class="form-group required">
                                        <label class="col-sm-2 control-label">Адрес</label>
                                        <input requiredt type="text" name="street" value="<?= (!empty($_POST['street'])) ? $_POST['street'] : (($cart->cc_street) ? $cart->cc_street : ''); ?>" placeholder="Адрес" class="form-control"/>
                                    </div>

                                    <div class ='form-group required'>
                                        <label>День доставки</label>
                                        <input required type ='text' name ='cc_day' class = 'form-control datepicker2'  required  placeholder = '' value ='<?= (!empty($_POST['cc_day'])) ? $_POST['cc_day'] : (($cart->cc_day) ? $cart->cc_day : date('d.m.Y')); ?>' /> 
                                    </div>
                                    <script>
                                        var curDate = new Date('<?= date('c'); ?>');
    <?php
    $deliverysArray = [];
    foreach ($deliverys as $key => $delivery) {
        $deliverysArray[$key] = $delivery->params;
    }
    ?>
                                        var deliverys = <?= json_encode($deliverysArray); ?>;
                                        $('.datepicker2').datepicker({
                                            language: 'ru',
                                            format: 'dd.mm.yyyy',
                                            startDate: '0d',
                                            endDate: '+7d'
                                        });
                                        $('.datepicker2').datepicker()
                                                .on('changeDate', function (e) {
                                                    if (e.date.getDate() == curDate.getDate()) {
                                                        checkTimedelivery()
                                                    }
                                                    else {
                                                        $('#time1015').removeAttr('disabled');
                                                        $('#time1621').removeAttr('disabled');
                                                    }
                                                });
                                        function checkTimedelivery() {
                                            if (curDate.getHours() >= 10) {
                                                $('#time1015').attr('disabled', 'disabled');
                                                $('#time1015')[0].selected = false;
                                            }
                                            if (curDate.getHours() >= 16) {
                                                $('#time1621').attr('disabled', 'disabled');
                                                $('#time1621')[0].selected = false;
                                            }
                                        }

                                        $(function () {
                                            checkTimedelivery();
                                        });
                                    </script>

                                    <div class ='form-group required'>
                                        <label>Время доставки</label>
                                        <select required name ='cc_time' class = 'form-control'>
                                            <option>Выберите</option>
                                            <option id = 'time1015' <?php if (!empty($_POST['cc_time']) && $_POST['cc_time'] == '10-15') echo 'selected'; ?> >10-15</option>
                                            <option id = 'time1621' <?php if (!empty($_POST['cc_time']) && $_POST['cc_time'] == '16-21') echo 'selected'; ?> >16-21</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="address">
                        <h2 class="secondary-title">Дополнительно</h2>
                        <div class=" checkout-payment-form">
                            <div class="form-horizontal form-payment">
                                <div id="payment-new" style="display: block;">
                                    <?php
                                    if ($packItem) {
                                        $packchecked = ((!empty($_POST) && empty($_POST['packs']))) ? '' : 'checked';
                                        ?>
                                        <div class ='form-group'>
                                            <div class = "checkbox">
                                                <label>
                                                    <input type = "checkbox" name = "packs" <?= $packchecked; ?> value ='<?= $packItem->price->ciprice_price; ?>' onchange ='calcsumm()' /> Добавить в заказ пакеты
                                                </label>
                                            </div>
                                            <div class="help-block">
                                                Вам понадобится: <b class="packsCount" ><?= ceil(($asum) / 1000); ?></b> <?= Inji::app()->tools->getNumEnding(ceil(($asum) / 1000), ['Пакет', 'Пакета', 'Пакетов']); ?><br />
                                                Стоимость одного пакета: <b><?= $packItem->price->ciprice_price; ?></b>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>                                    
            </div>

            <div class="right">
                <section class="section-left">
                    <div class="spw">
                        <div class="checkout-content checkout-shipping-methods">
                            <h2 class="secondary-title">Способ доставки</h2>
                            <?php
                            $first = true;
                            foreach ($deliverys as $delivery) {
                                if ($first === true) {
                                    $first = $delivery;
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }
                                ?>
                                <div class="radio">
                                    <label>
                                        <input name="delivery" value="<?= $delivery->cd_id; ?>" <?= $checked; ?> type="radio">
                                        <?= $delivery->cd_name; ?> - <?= $delivery->cd_price; ?> руб.
                                        <?php
                                        if ((float) $delivery->cd_max_cart_price) {
                                            echo '<br/><small>При заказе товаров на сумму от ' . $delivery->cd_max_cart_price . ' руб, доставка курьером - бесплатно</small>';
                                        }
                                        ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                        <div class="checkout-content checkout-payment-methods">
                            <h2 class="secondary-title">Способ оплаты</h2>

                            <?php
                            $firstPay = true;
                            foreach ($payTypes as $payType) {
                                if ((!empty($_POST['payType']) && $_POST['payType'] == $payType->cpt_id) || $firstPay === true) {
                                    $firstPay = $payType;
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }
                                ?>
                                <div class="radio">
                                    <label>
                                        <input name="payType" value="<?= $payType->cpt_id; ?>" <?= $checked; ?> type="radio">
                                        <?= $payType->cpt_name; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>            


                        </div>                        
                    </div>
                </section>
                <section class="section-right">
                    <div class="checkout-content checkout-cart">
                        <h2 class="secondary-title">Корзина товаров</h2>
                        <div class="table-responsive checkout-product">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <td class="text-left name" colspan="2">Название товара</td>
                                        <td class="text-left quantity" style="min-width: 225px">Количество</td>
                                        <td class="text-right price">Цена</td>
                                        <td class="text-right total">Итого</td>
                                    </tr>
                                </thead>
                                <tbody class="cartitems">
                                    <?php
                                    $i = 0;
                                    $summ = 0;
                                    foreach ($cart->cartItems as $cartItem) {

                                        if (!empty($cartItem->item->options['itemImage']) && $cartItem->item->options['itemImage']->file) {
                                            $path = $cartItem->item->options['itemImage']->file->file_path;
                                        } elseif (!empty($cartItem->item->options['image']) && $cartItem->item->options['image']->file) {
                                            $path = $cartItem->item->options['image']->file->file_path;
                                        } else {
                                            $path = '/static/images/no-image.png';
                                        }
                                        $summ += $cartItem->price->ciprice_price * $cartItem->cci_count;
                                        $itemName = (empty($cartItem->item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value)) ? $cartItem->item->ci_name : $cartItem->item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value;
                                        ?>
                                        <tr class="cci_id<?= $cartItem->cci_id; ?> item" data-cci_id = '<?php echo $cartItem->cci_id; ?>' data-priceam = '<?php echo $cartItem->price->ciprice_price; ?>' data-price = '<?php echo $cartItem->price->ciprice_id; ?>'>
                                            <td class="text-center image">                            
                                                <a href="/ecommerce/view/<?php echo $cartItem->item->ci_id; ?>">
                                                    <img src="<?= $path; ?>?resize=50x50" alt="<?= $itemName; ?>" title="<?= $itemName; ?>" class="img-thumbnail" />
                                                </a>
                                            </td>
                                            <td class="text-left name">
                                                <a href="/ecommerce/view/<?= $cartItem->item->ci_id; ?>"><?= $itemName; ?></a>
                                            </td>
                                            <td class="text-left quantity">
                                                <?php
                                                if (!empty($cartItem->item->options['7fc7a4d1-b26a-11e4-9490-80c16e818121']) && $cartItem->item->options['7fc7a4d1-b26a-11e4-9490-80c16e818121']->cip_value) {
                                                    echo '<div style="min-width:200px;">';
                                                    $price = $cartItem->price->ciprice_price;
                                                    $max = $cartItem->item->warehouseCount((!empty($_SESSION['cart']['cart_id']) ? $_SESSION['cart']['cart_id'] : 0)) * 1000;
                                                    $step = preg_replace('![^0-9]!', '', $cartItem->item->options['7fc7a4d1-b26a-11e4-9490-80c16e818121']->cip_value);
                                                    ?>
                                                    Примерный вес
                                                    <input name="items[<?php echo $cartItem->cci_ci_id; ?>]" type = "text" class ="combineRanger item-counter cart-couner rangerCount" data-step ="<?= $step; ?>" data-max="<?= $max; ?>" data-price ="<?= $price; ?>" value="<?php echo (float) $cartItem->cci_count * 1000; ?>" />
                                                    <?php
                                                    echo '</div>';
                                                } else {
                                                    ?>
                                                    <div class="input-group">
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-default btn-number btn-sm" data-type="minus" data-field="items[<?php echo $cartItem->cci_ci_id; ?>]">
                                                                <span class="glyphicon glyphicon-minus"></span>
                                                            </button>
                                                        </span>
                                                        <input style ="min-width:50px;float:none;line-height: 16px;margin:2px;vertical-align: middle;" type="text" name="items[<?php echo $cartItem->cci_ci_id; ?>]" class="form-control input-number input-sm cart-couner" value="<?php echo (float) $cartItem->cci_count; ?>" min="1" max="<?= $cartItem->item->warehouseCount((!empty($_SESSION['cart']['cart_id']) ? $_SESSION['cart']['cart_id'] : 0)); ?>">
                                                        <span class="input-group-btn" style ='border'>
                                                            <button type="button" class="btn btn-default btn-number btn-sm" data-type="plus" data-field="items[<?php echo $cartItem->cci_ci_id; ?>]">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                            </button>
                                                        </span>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                                <div class="input-group btn-block" style="max-width: 200px;">
                                                    <span class="input-group-btn">
                                                        <button onclick="calcsumm()" type="button" data-toggle="tooltip" title="Обновить" class="btn btn-primary btn-update"><i class="fa fa-refresh"></i></button>
                                                        <button type="button" data-toggle="tooltip" title="Удалить" class="btn btn-danger  btn-delete" onclick="cartdel(<?php echo $cartItem->cci_id; ?>);
                                                                        return
                                                                        false;"><i class="fa fa-times-circle"></i></button>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-right price"><?php echo $cartItem->price->ciprice_price; ?>&nbsp;руб.</td>
                                            <td class="text-right total"><?= $cartItem->price->ciprice_price * $cartItem->cci_count; ?>&nbsp;руб.</td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="cartsumms">
                                        <td colspan="4" class="text-right">Сумма:</td>
                                        <td class="text-right"><?= $summ; ?> руб.</td>
                                    </tr>
                                    <tr class="deliverysum">
                                        <td colspan="4" class="text-right"><?= $first->cd_name; ?>:</td>
                                        <td class="text-right"><?= ($summ >= $first->cd_max_cart_price) ? '0' : $first->cd_price; ?> руб.</td>
                                    </tr>
                                    <?php
                                    if ($packchecked) {
                                        $packSum = ceil(($asum) / 1000) * (float) $packItem->price->ciprice_price;
                                    }
                                    else {
                                        $packSum = 0;
                                    }
                                    ?>
                                    <tr class="packssum">
                                        <td colspan="4" class="text-right">Пакеты:</td>
                                        <td class="text-right"><?= $packSum ?> руб.</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right">Итого:</td>
                                        <td class="text-right"><?= $asum + $packSum; ?> руб.</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>


                    <div class="checkout-content confirm-section">
                        <div>
                            <h2 class="secondary-title">Вы можете добавить комментарий к своему заказу</h2>
                            <label>
                                <textarea name="cc_comment" rows="8" class="form-control"><?= (!empty($_POST['cc_comment'])) ? $_POST['cc_comment'] : ''; ?></textarea>
                            </label>
                        </div>
                        <div class="confirm-order">
                            <button id="journal-checkout-confirm-button" data-loading-text="Подождите.." class="button confirm-button">Подтверждение заказа</button>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </form>
    <?php
}
?>