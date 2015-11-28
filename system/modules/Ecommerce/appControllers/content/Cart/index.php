<div class="ecommerce">
  <div class="cart-order_page">
    <h2>Быстрое оформление заказа</h2>
    <?php
    if (!$cart || !$cart->cartItems)
        echo "<h1>Ваша корзина пуста</h1>";
    else {
        $cartDelivery = $cart->delivery;
        $deliveryPrice = 0;
        if (!$cartDelivery) {
            $cartDelivery = current($deliverys);
        }
        if ($cartDelivery) {
            $deliveryPrice = (($cart->sum >= $cartDelivery->max_cart_price) ? '0' : $cartDelivery->price);
        }
        $cartPayType = $cart->payType;
        if (!$cartPayType) {
            $cartPayType = current($payTypes);
        }

        $form = new Ui\Form;
        $form->action = "/ecommerce/cart";
        $form->begin();
        ?>
        <div class="row">
          <div class="col-sm-4">
            <div class="order_page-info">
              <?php if (!Users\User::$cur->id) { ?>
                  <fieldset id="account">
                    <h4>Аккаунт</h4>
                    <?php $this->widget('Ecommerce\cart/fastLogin', ['form' => $form, 'cart' => $cart]); ?>
                  </fieldset>
              <?php } ?>
              <?php if (Ecommerce\Card::getList()) { ?>
                  <fieldset id="discount">
                    <h4>Дисконтная карта</h4>
                    <?php $this->widget('Ecommerce\cart/cardSelect', ['form' => $form, 'cart' => $cart]); ?>
                  </fieldset>
              <?php } ?>
              <fieldset id="address">
                <h4>Информация для доставки</h4>
                <?php $this->widget('Ecommerce\cart/fields', ['form' => $form, 'cart' => $cart]); ?>
              </fieldset>
              <?php
              $packchecked = '';
              $packItem = false;
              $packSum = 0;
              if ($packItem) {
                  $packchecked = ((!empty($_POST) && empty($_POST['packs']))) ? '' : 'checked';
                  ?>
                  <fieldset id="additional">
                    <h2 class="secondary-title">Дополнительно</h2>
                    <div class=" checkout-payment-form">
                      <div class="form-horizontal form-payment">
                        <div id="payment-new" style="display: block;">

                          <div class ='form-group'>
                            <div class = "checkbox">
                              <label>
                                <input type = "checkbox" name = "packs" <?= $packchecked; ?> value ='<?= $packItem->price->price; ?>' onchange ='calcsum()' /> Добавить в заказ пакеты
                              </label>
                            </div>
                            <div class="help-block">
                              Вам понадобится: <b class="packsCount" ><?= ceil(($cart->sum + $deliveryPrice) / 1000); ?></b> <?= Tools::getNumEnding(ceil(($cart->sum + $deliveryPrice) / 1000), ['Пакет', 'Пакета', 'Пакетов']); ?><br />
                              Стоимость одного пакета: <b><?= $packItem->price->price; ?></b>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </fieldset>
                  <?php
              }
              ?>
            </div>                                    
          </div>
          <div class="col-sm-8">
            <div class="order_page-options">
              <div class="row">
                <div class="col-sm-6">
                  <div class="order_page-delivery">
                    <h4>Способ доставки</h4>
                    <?php
                    foreach ($deliverys as $delivery) {
                        if ((!empty($_POST['delivery']) && $_POST['delivery'] == $delivery->id) || $delivery == $cartDelivery) {
                            $checked = 'checked';
                        } else {
                            $checked = '';
                        }
                        $helpText = '';
                        if ((float) $delivery->max_cart_price) {
                            $helpText.= 'При заказе товаров на сумму от ' . $delivery->max_cart_price . ' руб - бесплатно';
                        }
                        if ($delivery->info) {
                            if ($helpText) {
                                $helpText .= '<br />';
                            }
                            $helpText .= nl2br($delivery->info);
                        }
                        $form->input('radio', "delivery", $delivery->name . ((float) $delivery->price ? ' - ' . $delivery->price . ' ' . ($delivery->currency ? $delivery->currency->acronym() : 'руб.') : ''), [
                            'value' => $delivery->id,
                            'checked' => $checked,
                            'helpText' => $helpText
                        ]);
                    }
                    ?>
                  </div>
                </div>
                <div class="checkout-content checkout-payment-methods">
                  <h4>Способ оплаты</h4>
                  <?php
                  foreach ($payTypes as $payType) {
                      if ((!empty($_POST['payType']) && $_POST['payType'] == $payType->id) || $payType == $cartPayType) {
                          $checked = 'checked';
                      } else {
                          $checked = '';
                      }
                      $form->input('radio', "payType", $payType->name, ['value' => $payType->id, 'checked' => $checked]);
                  }
                  ?>            
                </div>                        
              </div>
            </div>
            <div class="order_page-details">
              <h3>Корзина товаров</h3>
              <div class="table-responsive">
                <table class="table table-bordered order_page-cartItems">
                  <thead>
                    <tr>
                      <td colspan="2">Название товара</td>
                      <td>Количество</td>
                      <td>Цена</td>
                      <?= $cart->card ? '<td>Скидка</td>' : ''; ?>
                      <td>Итого</td>
                      <td style="width:1%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if (class_exists('Money\Currency')) {
                        $defaultCurrency = Money\Currency::get(\App::$cur->ecommerce->config['defaultCurrency']);
                    } else {
                        $defaultCurrency = '';
                    }
                    $discountSum = 0;
                    foreach ($cart->cartItems as $cartItem) {
                        $path = $cartItem->item->image ? $cartItem->item->image->path : '/static/system/images/no-image.png';
                        $discount = $cartItem->discount();
                        $discountSum += $discount;
                        $itemName = $cartItem->item->name();
                        ?>
                        <tr class="cart_item_id<?= $cartItem->id; ?> item" data-cart_item_id = '<?php echo $cartItem->id; ?>' data-priceam = '<?php echo $cartItem->price->price; ?>' data-item_offer_price_id = '<?php echo $cartItem->price->id; ?>'>
                          <td class="text-center image">                            
                            <a href="/ecommerce/view/<?php echo $cartItem->item->id; ?>">
                              <img src="<?= $path; ?>?resize=50x50" alt="<?= $itemName; ?>" title="<?= $itemName; ?>" class="img-thumbnail" />
                            </a>
                          </td>
                          <td class="text-left name">
                            <a href="/ecommerce/view/<?= $cartItem->item->id; ?>"><?= $itemName . ($cartItem->price->offer->name ? ' (' . $cartItem->price->offer->name() . ')' : ''); ?></a>
                          </td>
                          <td class="text-left quantity">
                            <?php
                            $options = $cartItem->item->options(['key' => 'item_option_id']);
                            $price = $cartItem->item->getPrice();
                            if (empty(App::$cur->ecommerce->config['sell_over_warehouse'])) {
                                $max = $price->offer->warehouseCount((!empty($_SESSION['cart']['cart_id']) ? $_SESSION['cart']['cart_id'] : 0));
                            } else {
                                $max = 100;
                            }
                            if (!empty($options[16]) && $options[16]->value) {
                                echo '<div style="min-width:200px;">';
                                $price = $cartItem->price;
                                $step = preg_replace('![^0-9]!', '', $options[16]->value) / 1000;
                                ?>
                                Примерный вес
                                <input type = "text" data-miltiple="1000" class ="combineRanger item-counter" data-step ="<?= $step; ?>" data-max="<?= $max; ?>" data-price ="<?= $price->price; ?>" name="cartItems[<?php echo $cartItem->id; ?>]" value ="<?= $cartItem->count; ?>" />
                                <?php
                                echo '</div>';
                            } else {
                                ?>
                                <div class="input-group number-spinner" >
                                  <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm btn-number" data-type="minus" data-field="cartItems[<?php echo $cartItem->id; ?>]"><span class="glyphicon glyphicon-minus"></span></button>
                                  </span>
                                  <input type="text" name="cartItems[<?php echo $cartItem->id; ?>]" class="form-control text-center input-sm input-number" value="<?php echo (float) $cartItem->count; ?>" min="1" max="<?= $max; ?>">
                                  <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm btn-number" data-type="plus" data-field="cartItems[<?php echo $cartItem->id; ?>]"><span class="glyphicon glyphicon-plus"></span></button>
                                  </span>
                                </div>
                                <?php
                            }
                            ?>

                          </td>
                          <td class="text-right price"><?= number_format($cartItem->price->price, 2, '.', '&nbsp;'); ?>&nbsp;<?= $cartItem->price->currency ? $cartItem->price->currency->acronym() : ($defaultCurrency ? $defaultCurrency->acronym() : 'Руб.'); ?></td>
                          <?php if ($cart->card) { ?>
                              <td class="text-right discount"><?= number_format($discount, 2, '.', '&nbsp;'); ?>&nbsp;<?= $cartItem->price->currency ? $cartItem->price->currency->acronym() : ($defaultCurrency ? $defaultCurrency->acronym() : 'Руб.'); ?></td>
                          <?php } ?>
                          <td class="text-right total"><?= number_format($cartItem->price->price * $cartItem->count - $discount, 2, '.', '&nbsp;'); ?>&nbsp;<?= $cartItem->price->currency ? $cartItem->price->currency->acronym() : ($defaultCurrency ? $defaultCurrency->acronym() : 'Руб.'); ?></td>
                          <td class="text-right actions">
                            <div class="btn-group-vertical" role="group" aria-label="...">
                              <a type="button" class="btn btn-primary btn-update btn-sm" onclick="inji.Ecommerce.Cart.calcSum();"><i class="glyphicon glyphicon-refresh"></i></a>
                              <a type="button" class="btn btn-danger  btn-delete btn-sm" onclick="inji.Ecommerce.Cart.delItem(<?php echo $cartItem->id; ?>);"><i class="glyphicon glyphicon-remove"></i></a>
                            </div>
                          </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tr>
                  </tbody>
                  <tfoot>
                    <?php
                    $colspan = $cart->card ? 5 : 4;
                    ?>
                    <tr class="order_page-sum">
                      <td colspan="<?= $colspan; ?>" class="text-right">Сумма:</td>
                      <td colspan="2" class="text-right"><?= number_format($cart->sum, 2, '.', ' '); ?>&nbsp;руб.</td>
                    </tr>
                    <?php
                    if ($cartDelivery) {
                        ?>
                        <tr class="order_page-deliverySum">
                          <td colspan="<?= $colspan; ?>" class="text-right"><?= $cartDelivery->name; ?>:</td>
                          <td colspan="2" class="text-right"><?= number_format($deliveryPrice, 2, '.', ' '); ?>&nbsp;руб.</td>
                        </tr>
                        <?php
                    }
                    if ($packItem) {
                        if ($packchecked) {
                            $packSum = ceil(($cart->sum + $deliveryPrice) / 1000) * (float) $packItem->price->price;
                        } else {
                            $packSum = 0;
                        }
                        ?>
                        <tr class="order_page-packSum">
                          <td colspan="<?= $colspan; ?>" class="text-right">Пакеты:</td>
                          <td colspan="2" class="text-right"><?= number_format($packSum, 2, '.', ' '); ?>&nbsp;руб.</td>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php if ($cart->card) { ?>
                        <tr class="order_page-discountSum">
                          <td colspan="<?= $colspan; ?>" class="text-right">Скидка:</td>
                          <td colspan="2" class="text-right"><?= number_format($discountSum, 2, '.', ' '); ?>&nbsp;руб.</td>
                        </tr>
                    <?php } ?>
                    <tr class="order_page-total">
                      <td colspan="<?= $colspan; ?>" class="text-right">Итого:</td>
                      <td colspan="2" class="text-right"><?= number_format($cart->sum + $deliveryPrice + $packSum - $discountSum, 2, '.', ' '); ?>&nbsp;руб.</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <hr />
            <div class="order_page-finish">
              <?php
              $form->input('textarea', 'comment', 'Вы можете добавить комментарий к своему заказу', ['value' => (!empty($_POST['comment'])) ? $_POST['comment'] : '']);
              ?>
              <div class="order_page-orderBtn">
                <button name ="action" value ="order" data-loading-text="Подождите.." class="btn btn-primary">Подтверждение заказа</button>
              </div>
            </div>
          </div>
        </div>
        <?php
        $form->end(false);
    }
    ?>
  </div>
</div>