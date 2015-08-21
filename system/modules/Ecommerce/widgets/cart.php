<?php
if (!empty($_SESSION['cart']['cart_id'])) {
    $cart = Ecommerce\Cart::get((int) $_SESSION['cart']['cart_id']);
} else {
    $cart = false;
}
?>
<div id="cart" class="btn-group btn-block"> 
    <button type="button" data-toggle="dropdown" class="btn btn-inverse btn-block btn-lg dropdown-toggle heading">
        <a><span id="cart-total" data-loading-text="Загрузка...&nbsp;&nbsp;">Товаров <?= $cart ? count($cart->cartItems) : 0; ?> (<?= $cart ? $cart->sum : 0; ?>р.)</span> <i></i></a>
    </button> 
    <div class="content"> 
        <ul class="cart-wrapper"> 
            <?php
            if (!$cart || !$cart->cartItems) {
                ?>
                <li> 
                    <p class="text-center empty">Ваша корзина пуста!</p> 
                </li> 
                <?php
            } else {
                ?>
                <li class="mini-cart-info">
                    <table class="table table-striped">
                        <?php
                        foreach ($cart->cartItems as $cartItem) {
                            $item = $cartItem->item;
                            $itemName = $item->name();
                            $path = $item->image ? $item->image->path : '/static/system/images/no-image.png';
                            ?>
                            <tr class ='cart_item_id<?=$cartItem->id;?>'>
                                <td class="text-center image">            
                                    <a href="/ecommerce/view/<?= $item->id; ?>"><img src="<?= $path; ?>?resize=47x47&resize_crop=q" alt="<?= $itemName; ?>" title="<?= $itemName; ?>" class="img-thumbnail" /></a>
                                </td>
                                <td class="text-left name"><a href="/ecommerce/view/<?= $item->id; ?>"><?= $itemName; ?></a>
                                    <div>
                                    </div>
                                </td>
                                <td class="text-right quantity">x <?= $cartItem->count; ?></td>
                                <td class="text-right total"><?php
                                    ?>
                                    <?= $cartItem->price->price; ?>р.</td>
                                <td class="text-center remove"><button type="button" onclick="cartdel(<?=$cartItem->id;?>);" title="Удалить" class=""><i class=""></i></button></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </li>
                <li>
                    <div class="mini-cart-total">
                        <table class="table table-bordered">
                            <tr>
                                <td class="text-right right"><strong>Сумма</strong></td>
                                <td class="text-right right"><?= $cart->sum; ?>р.</td>
                            </tr>
                            <tr>
                                <td class="text-right right"><strong>Итого</strong></td>
                                <td class="text-right right"><?= $cart->sum; ?>р.</td>
                            </tr>
                        </table>
                        <p class="text-right checkout"><a class="button" href="/ecommerce/cart">Перейти в корзину</a>&nbsp;<a class="button" href="/ecommerce/cart">Оформить заказ</a></p>
                    </div>
                </li>
                <?php
            }
            ?>
        </ul> 
    </div> 
</div> 