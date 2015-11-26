<?php
$pages = new Ui\Pages($_GET, ['count' => Ecommerce\Cart::getCount(['where' => ['user_id', Users\User::$cur->id]]), 'limit' => 10]);
$carts = Ecommerce\Cart::getList(['where' => ['user_id', Users\User::$cur->id], 'order' => ['date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);
?>
<h3>История заказов</h3>
<div class="table-responsive">
  <table class="table table-bordered table-hover list">
    <thead>
      <tr>
        <td class="text-right">№ Заказа</td>
        <td class="text-left">Статус</td>
        <td class="text-left">Добавлено</td>
        <td class="text-right">Товары</td>
        <td class="text-right">Всего</td>
        <td></td>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($carts as $cart) {
          $sums = [];
          foreach ($cart->cartItems as $cartItem) {
              $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
              if (empty($sums[$currency_id])) {
                  $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
              } else {
                  $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
              }
          }
          ?>
          <tr>
            <td class="text-right">#<?= $cart->id; ?></td>
            <td class="text-left"><?= $cart->status ? $cart->status->name : 'Наполняется'; ?></td>

            <td class="text-left"><?= $cart->complete_data; ?></td>
            <td class="text-right"><?= count($cart->cartItems); ?></td>
            <td class="text-right"><?php
              foreach ($sums as $currency_id => $sum) {
                  echo $sum . ' ';
                  if (App::$cur->money) {
                      $currency = Money\Currency::get($currency_id);
                      if ($currency) {
                          echo $currency->acronym();
                      } else {
                          echo 'руб.';
                      }
                  } else {
                      echo 'руб.';
                  }
              }
              ?></td>
            <td class="text-right">
              <?php
              if ($cart->cart_status_id < 2) {
                  ?>
                  <a title="Продолжить покупки" href="/ecommerce/cart/continue/<?= $cart->id; ?>" data-toggle="tooltip" title="" class="btn btn-success"><i class="glyphicon glyphicon-chevron-right"></i></a>
                  <a title="Удалить заказ" href="/ecommerce/cart/delete/<?= $cart->id; ?>" data-toggle="tooltip" title="" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i></a>
                  <?php
              }
              if ($cart->cart_status_id >= 2) {
                  ?>
                  <a title="Просмотр" href="/ecommerce/cart/orderDetail/<?= $cart->id; ?>" data-toggle="tooltip" title="" class="btn btn-info "><i class="glyphicon glyphicon-eye-open"></i></a>
                  <a title="Заказать повторно" href="/ecommerce/cart/refill/<?= $cart->id; ?>" data-toggle="tooltip" title="" class="btn btn-primary "><i class="glyphicon glyphicon-refresh"></i></a>
                    <?php
                }
                ?>
            </td>
          </tr>
          <?php
      }
      ?>
    </tbody>
  </table>
</div>
<?php
$pages->draw();
?>
