<div class="ecommerce">
  <div class="content">
    <div class="cart-order_page">
      <h2>Быстрое оформление заказа</h2>
      <?php
      if (!$cart || !$cart->cartItems)
          echo "<h1>Ваша корзина пуста</h1>";
      else {
          $sums = [];
          $cartDelivery = $cart->delivery;
          $deliveryPrice = 0;
          $deliveryCurrency = 0;
          if (!$cartDelivery) {
              $cartDelivery = current($deliverys);
          }
          if ($cartDelivery) {
              if ($cartDelivery->max_cart_price) {
                  $deliveryPrice = (($cart->sum >= $cartDelivery->max_cart_price) ? '0' : $cartDelivery->price);
              } else {
                  $deliveryPrice = $cartDelivery->price;
              }
              $deliveryCurrency = $cartDelivery->currency_id;
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
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h3 class="panel-title">Аккаунт</h3>
                      </div>
                      <div class="panel-body">
                        <?php $this->widget('Ecommerce\cart/fastLogin', ['form' => $form, 'cart' => $cart]); ?>
                      </div>
                    </div>
                <?php } ?>
                <?php if (Ecommerce\Card::getList()) { ?>
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h3 class="panel-title">Дисконтная карта</h3>
                      </div>
                      <div class="panel-body">
                        <?php $this->widget('Ecommerce\cart/cardSelect', ['form' => $form, 'cart' => $cart]); ?>
                      </div>
                    </div>
                <?php } ?>
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title">Контактная информация</h3>
                  </div>
                  <div class="panel-body">
                    <?php $this->widget('Ecommerce\cart/fields', ['form' => $form, 'cart' => $cart]); ?>
                  </div>
                </div>
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
                <div class="order_page-delivery">
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h3 class="panel-title">Способ доставки</h3>
                    </div>
                    <div class="panel-body">
                      <?php $this->widget('Ecommerce\cart/delivery', compact('form', 'cart', 'deliverys', 'cartDelivery')); ?>
                    </div>
                  </div>
                </div>
                <div class="checkout-content checkout-payment-methods">
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h3 class="panel-title">Способ оплаты</h3>
                    </div>
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-4">
                          <ul class="nav nav-pills nav-stacked">
                            <?php
                            $hiddenId = Tools::randomString();
                            foreach ($payTypes as $payType) {
                                if ((!empty($_POST['payType']) && $_POST['payType'] == $payType->id) || $payType->id == $cartPayType->id) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }
                                echo '<li' . ($checked ? ' class="active"' : '') . '><a href = "#" onclick = "document.getElementById(\'' . $hiddenId . '\').value=\'' . $payType->id . '\';inji.Ecommerce.Cart.calcSum();return false;">';
                                echo $payType->name;
                                echo '</a></li>';
                            }
                            $form->input('hidden', "payType", '', [
                                'value' => $cartPayType->id,
                                'attributes' => [
                                    'id' => $hiddenId
                                ],
                            ]);
                            ?>
                          </ul>
                        </div>
                        <div class="col-md-8">
                          <h4>Информация об оплате</h4>
                          <?php
                          echo $cartPayType->info;
                          ?> 
                        </div>
                      </div>
                    </div>
                  </div>      
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12">
              <div class="order_page-details">
                <h3>Корзина товаров</h3>
                <div class="table-responsive">
                  <table class="table table-bordered order_page-cartItems">
                    <thead>
                      <tr>
                        <td colspan="2">Название товара</td>
                        <td style="width:1%;min-width:150px;">Количество</td>
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
                          if (!isset($sums[$cartItem->price->currency_id])) {
                              $sums[$cartItem->price->currency_id] = $cartItem->price->price * $cartItem->count;
                          } else {
                              $sums[$cartItem->price->currency_id] += $cartItem->price->price * $cartItem->count;
                          }
                          ?>
                          <tr class="cart_item_id<?= $cartItem->id; ?> item" data-cart_item_id = '<?php echo $cartItem->id; ?>' data-priceam = '<?php echo $cartItem->price->price; ?>' data-item_offer_price_id = '<?php echo $cartItem->price->id; ?>'>
                            <td class="text-center image">                            
                              <a href="/ecommerce/view/<?php echo $cartItem->item->id; ?>">
                                <img src="<?= $path; ?>?resize=50x50" alt="<?= htmlspecialchars($itemName); ?>" title="<?= htmlspecialchars($itemName); ?>" />
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
                        <td colspan="2" class="text-right"><?php
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
                                      echo '&nbsp;руб.';
                                  }
                              } else {
                                  echo '&nbsp;руб.';
                              }
                              echo '<br />';
                          }
                          ?></td>
                      </tr>
                      <?php
                      if ($cartDelivery) {
                          ?>
                          <tr class="order_page-deliverySum">
                            <td colspan="<?= $colspan; ?>" class="text-right"><?= $cartDelivery->name; ?>:</td>
                            <td colspan="2" class="text-right"><?php
                              if ($cartDelivery && $cartDelivery->price_text) {
                                  echo $cartDelivery->price_text;
                              } else {
                                  echo number_format($deliveryPrice, 2, '.', ' ');
                                  if ($deliveryCurrency && App::$cur->money) {
                                      $currency = Money\Currency::get($deliveryCurrency);
                                      if ($currency) {
                                          echo '&nbsp;' . $currency->acronym();
                                      } else {
                                          echo '&nbsp;руб.';
                                      }
                                  } else {
                                      echo '&nbsp;руб.';
                                  }
                              }
                              ?></td>
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
                      if ($cart->card) {
                          ?>
                          <tr class="order_page-discountSum">
                            <td colspan="<?= $colspan; ?>" class="text-right">Скидка:</td>
                            <td colspan="2" class="text-right"><?= number_format($discountSum, 2, '.', ' '); ?>&nbsp;руб.</td>
                          </tr>
                          <?php
                      }
                      if (!isset($sums[$deliveryCurrency])) {
                          $sums[$deliveryCurrency] = $deliveryPrice;
                      } else {
                          $sums[$deliveryCurrency] += $deliveryPrice;
                      }
                      ?>
                      <tr class="order_page-total">
                        <td colspan="<?= $colspan; ?>" class="text-right">Итого:</td>
                        <td colspan="2" class="text-right"><?php
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
                                      echo '&nbsp;руб.';
                                  }
                              } else {
                                  echo '&nbsp;руб.';
                              }
                              echo '<br />';
                          }
                          ?></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <hr />
                <div class="clearfix"></div>
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
          </div>

          <?php
          $form->end(false);
      }
      ?>
    </div>
  </div>
</div>