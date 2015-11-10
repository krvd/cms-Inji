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
          ?>

          <tr>
            <td class="text-right">#<?= $cart->id; ?></td>
            <td class="text-left"><?= $cart->status ? $cart->status->name : 'Наполняется'; ?></td>

            <td class="text-left"><?= $cart->complete_data; ?></td>
            <td class="text-right"><?= count($cart->cartItems); ?></td>
            <td class="text-right"><?= $cart->sum; ?>р.</td>
            <td class="text-right">
              <a data-original-title="Просмотр" href="/ecommerce/cart/orderDetail/<?= $cart->id; ?>" data-toggle="tooltip" title="" class="btn btn-info btn-primary"><i class="glyphicon glyphicon-eye-open"></i></a>
                <?php
                /*
                  if ($cart->cc_status <= 1) {
                  echo "<a class = 'btn btn-success btn-sm' href = '/ecommerce/cart/continue/{$cart->cc_id}'>Продолжить покупки</a>";
                  echo "<a class = 'btn btn-danger btn-sm' href = '/ecommerce/cart/delete/{$cart->cc_id}'>Удалить корзину</a>";
                  } else {
                  echo "<a class = 'btn btn-primary btn-sm' href = '/ecommerce/cart/refill/{$cart->cc_id}'>Повторить покупку</a>";
                  }
                  if (in_array($cart->cc_status, [4])) {
                  echo "<a class = 'btn btn-danger btn-sm' href = '/ecommerce/cart/delete/{$cart->cc_id}'>Удалить корзину</a>";
                  }
                 * 
                 */
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
