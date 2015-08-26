
<h1 class="heading-title">Заказ</h1>
<table class="table table-bordered table-hover list">
    <thead>
        <tr>
            <td class="text-left" colspan="2">Детали заказа</td>
        </tr>
    </thead>
    <?php
    $orderDeatilCols = [
        'cc_id' => '№ Заказа',
        'cc_complete_data' => 'Дата заказа'
    ];
    $addCols = [
        'cc_fio' => 'ФИО',
        'cc_tel' => 'Телефон',
        'cc_email' => 'E-Mail',
        'cc_city' => 'Город',
        'cc_street' => 'Адрес',
        'cc_date' => 'Дата доставки',
        'cc_time' => 'Время доставки'
    ];
    ?>
    <tbody>
        <tr>
            <td class="text-left" style="width: 50%;">              
                <?php
                foreach ($orderDeatilCols as $col => $label) {
                    echo "<b>{$label}</b> " . $cart->$col . "<br>";
                }
                ?>
            </td>
            <td class="text-left">              
                <b>Способ оплаты</b> <?= $cart->payType ? $cart->payType->cpt_name : '' ?><br>
                <b>Способ доставки</b> <?= $cart->delivery ? $cart->delivery->cd_name : '' ?>              
            </td>
        </tr>
    </tbody>
</table>
<table class="table table-bordered table-hover list">
    <thead>
        <tr>
            <td class="text-left">Адрес доставки</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-left">
                <?php
                foreach ($addCols as $col => $label) {
                    echo "<b>{$label}</b> " . $cart->$col . "<br>";
                }
                ?>
            </td>
        </tr>
    </tbody>
</table>
<div class="table-responsive">
    <table class="table table-bordered table-hover list">
        <thead>

            <tr>
                <td class="text-left">Название товара</td>
                <td class="text-right">Количество</td>
                <td class="text-right">Цена</td>
                <td class="text-right">Всего</td>
                <td style="width: 20px;"></td>
            </tr>


        </thead>
        <tbody>
            <?php
            $sum = 0;
            foreach ($cart->cartItems as $cartItem) {
                $item = $cartItem->item;
                $itemName = (empty($cartItem->item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value)) ? $cartItem->item->ci_name : $item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value;
                $sum +=$cartItem->price->ciprice_price * $cartItem->cci_count;
                ?>
                <tr>
                    <td class="text-left"><?= $itemName; ?></td>
                    <td class="text-right"><?= $cartItem->cci_count; ?></td>
                    <td class="text-right"><?= $cartItem->price->ciprice_price; ?>р.</td>
                    <td class="text-right"><?= ($cartItem->price->ciprice_price * $cartItem->cci_count); ?>р.</td>
                    <td class="text-right" style="white-space: nowrap;">
                        <a onclick = 'addToCartJournal("<?= $item->ci_id; ?>", 1);
                                    return false;' data-original-title="Добавить в корзину" href="#" data-toggle="tooltip" title="" class="btn btn-primary"><i class="fa fa-shopping-cart"></i></a>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td class="text-right"><b>Сумма</b></td>
                <td class="text-right"><?= $sum; ?>р.</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="text-right"><b>Доставка с фиксированной стоимостью доставки</b></td>
                <td class="text-right"><?= (!$cart->delivery || $sum >= $cart->delivery->cd_max_cart_price) ? '0' : $cart->delivery->cd_price; ?>р.</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td class="text-right"><b>Итого</b></td>
                <td class="text-right"><?= $cart->sum; ?>р.</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<h3>История заказов</h3>
<table class="table table-bordered table-hover list">
    <thead>
        <tr>
            <td class="text-left">Добавлено</td>
            <td class="text-left">Тип</td>
            <td class="text-left">Значение</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $statuses = CartEvent::get_list(['where' => [['ece_cc_id', $cart->cc_id]], 'order' => ['ece_date', 'desc']]);
        foreach ($statuses as $status) {
            ?>
            <tr>
                <td class="text-left"><?= $status->ece_date; ?></td>
                <td class="text-left"><?= $status->type->ecet_name; ?></td>
                <td class="text-left">
                    <?php
                    switch ($status->ece_ecet_id) {
                        case'1':
                        case'2':
                            $item = ItemPrice::get($status->ece_info)->item;
                            $itemName = (empty($item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value)) ? $item->ci_name : $item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value;
                            echo "<a href = '/ecommerce/view/{$item->ci_id}'>{$itemName}</a>";
                            break;
                        case '4':
                            $info = explode('|', $status->ece_info);
                            $item = ItemPrice::get($info[0])->item;
                            $itemName = (empty($item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value)) ? $item->ci_name : $item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value;
                            echo "<a href = '/ecommerce/view/{$item->ci_id}'>{$itemName}</a> " . ($info[1]>0 ? '+' . $info[1] : $info[1]);
                            break;
                        case 5:
                            echo CartStatus::get($status->ece_info)->ccs_name;
                            break;
                        default :
                            echo $status->ece_info;
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>        


    </tbody>
</table>
