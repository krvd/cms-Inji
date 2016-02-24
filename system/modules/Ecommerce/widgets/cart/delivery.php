<div class="row">
  <div class="col-md-4">
    <ul class="nav nav-pills nav-stacked">
      <?php
      $hiddenId = Tools::randomString();
      foreach ($deliverys as $delivery) {
          if ((!empty($_POST['delivery']) && $_POST['delivery'] == $delivery->id) || $delivery->id == $cartDelivery->id) {
              $checked = 'checked';
          } else {
              $checked = '';
          }
          echo '<li' . ($checked ? ' class="active"' : '') . '><a href = "#" onclick = "document.getElementById(\'' . $hiddenId . '\').value=\'' . $delivery->id . '\';inji.Ecommerce.Cart.calcSum();return false;">';
          echo $delivery->name;
          echo '</a></li>';
      }
      $form->input('hidden', "delivery", '', [
          'value' => $cartDelivery->id,
          'attributes' => [
              'id' => $hiddenId
          ],
      ]);
      ?>
    </ul>
  </div>
  <div class="col-md-8">
    <h4>Информация о доставке</h4>
    <?php
    $delivery = $cartDelivery;
    if ($delivery->price_text || $delivery->price) {
        echo "<div>Стоимость доставки: <b>" . ($delivery->price_text ? $delivery->price_text : ( $delivery->price . ' ' . ($delivery->currency ? $delivery->currency->acronym() : 'руб.') )) . '</b></div>';
    }
    if ((float) $delivery->max_cart_price) {
        echo '<div>При заказе товаров на сумму от ' . $delivery->max_cart_price . ' руб - бесплатно</div>';
    }
    echo $delivery->info;
    if($delivery->fields) {
        echo '<hr \>';
        foreach ($delivery->fields as $field) {
            $form->input($field->type, "deliveryFields[{$field->id}]", $field->name, ['required' => $field->required]);
        }
    }
    ?>
  </div>
</div>