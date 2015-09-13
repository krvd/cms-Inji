<?php

class CartController extends Controller {

    function indexAction() {
        $cart = '';
        $deliverys = \Ecommerce\Delivery::getList();
        $payTypes = \Ecommerce\PayType::getList();
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Ecommerce\Cart::get($_SESSION['cart']['cart_id']);
            if (!empty($_POST)) {
                $error = false;
                if (!Users\User::$cur->id) {
                    $user_id = $this->Users->registration($_POST, true);
                    if (!$user_id) {
                        $error = true;
                    } else {
                        $user = Users\User::get($user_id);
                    }
                } else {
                    $user = Users\User::$cur;
                }
                $ids = [];
                foreach ($_POST['cartItems'] as $cartItemId => $cartItemCont) {
                    $cartItem = \Ecommerce\Cart\Item::get((int) $cartItemId);
                    if (!$cartItem) {
                        continue;
                    }
                    if ($cartItem->cart_id != $cart->id) {
                        continue;
                    }
                    $count = (float) $cartItemCont;
                    if ($count < 0.001) {
                        $count = 1;
                    }
                    $cartItem->count = $count;
                    $cartItem->save();
                    $ids[] = $cartItemId;
                }
                foreach ($cart->cartItems as $cartItem) {
                    if (!in_array($cartItem->id, $ids)) {
                        $cartItem->delete();
                    }
                }
                $cart = Ecommerce\Cart::get($cart->id);

                if (empty($this->module->config['sell_over_warehouse'])) {
                    foreach ($cart->cartItems as $cartitem) {
                        $warecount = $cartitem->price->offer->warehouseCount($cart->id);
                        if ($cartitem->count > $warecount) {
                            $error = true;
                            Msg::add('Вы заказали <b>' . $cartitem->item->name . '</b> больше чем есть на складе. на складе: <b>' . $warecount . '</b>', 'danger');
                        }
                    }
                }
                if (empty($deliverys[$_POST['delivery']])) {
                    $error = 1;
                    Msg::add('Ошибка при выборе способа доставки');
                }
                if (empty($payTypes[$_POST['payType']])) {
                    $error = 1;
                    Msg::add('Ошибка при выборе способа оплаты');
                }
                $fields = \Ecommerce\UserAdds\Field::getList();
                foreach ($fields as $field) {
                    if (empty($_POST['userAdds']['fields'][$field->id]) && $field->required) {
                        $error = 1;
                        Msg::add('Вы не указали: ' . $field->name);
                    }
                }
                $card_item_id = 0;
                if (!empty($_POST['discounts']['card_item_id'])) {
                    $userCard = \Ecommerce\Card\Item::get((int) $_POST['discounts']['card_item_id']);
                    if (!$userCard) {
                        $error = true;
                        Msg::add('Такой карты не существует');
                    } elseif ($userCard->user_id != $user->id) {
                        $error = true;
                        Msg::add('Это не ваша карта');
                    } else {
                        $cart->card_item_id = $userCard->id;
                        $cart->save();
                    }
                }

                if (!$error && !empty($_POST['action']) && $_POST['action'] = 'order') {
                    $cart->user_id = $user->user_id;
                    $this->module->parseFields($_POST['userAdds']['fields'], $cart);
                    $cart->cart_status_id = 2;
                    $cart->comment = htmlspecialchars($_POST['comment']);
                    $cart->date_status = date('Y-m-d H:i:s');
                    $cart->complete_data = date('Y-m-d H:i:s');
                    $cart->paytype_id = (int) $_POST['payType'];
                    $cart->delivery_id = (int) $_POST['delivery'];
                    $cart->warehouse_block = 1;
                    $cart->save();

                    $cart = \Ecommerce\Cart::get($cart->id);
                    foreach ($cart->cartItems as $cartItem) {
                        $cartItem->discount =  $cartItem->discount();
                        $cartItem->final_price = $cartItem->price->price - $cartItem->discount;
                        $cartItem->save();
                    }
                    unset($_SESSION['cart']['cart_id']);
                    Tools::redirect('/ecommerce/cart/success');
                }
            }
        }
        $this->view->setTitle('Корзина');
        $bread = [];
        $bread[] = [
            'text' => 'Каталог',
            'href' => '/ecommerce'
        ];
        $bread[] = [
            'text' => 'Корзина',
            'href' => '/ecommerce/cart'
        ];
        $this->view->page(['data' => compact('cart', 'items', 'deliverys', 'payTypes', 'packItem', 'bread')]);
    }

    function historyAction() {
        $this->view->setTitle('История');
        if (!Users\User::$cur->id)
            $this->url->redirect('/', 'Вы должны войти или зарегистрироваться');

        $pages = new Ui\Pages($_GET, ['count' => Ecommerce\Cart::getCount(['where' => ['user_id', Users\User::$cur->id]]), 'limit' => 10]);
        $carts = Ecommerce\Cart::getList(['where' => ['user_id', Users\User::$cur->id], 'order' => ['date_create', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);
        $bread = [];
        $bread[] = [
            'text' => 'Каталог',
            'href' => '/ecommerce'
        ];
        $bread[] = [
            'text' => 'Корзина',
            'href' => '/ecommerce/cart'
        ];
        $bread[] = [
            'text' => 'История заказов',
            'href' => '/ecommerce/cart/history'
        ];
        $this->view->page(['data' => compact('carts', 'pages', 'bread')]);
    }

    function orderDetailAction($id = 0) {
        $cart = Ecommerce\Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        $bread[] = [
            'text' => 'Каталог',
            'href' => '/ecommerce'
        ];
        $bread[] = [
            'text' => 'Корзина',
            'href' => '/ecommerce/cart'
        ];
        $bread[] = [
            'text' => 'Заказ: №' . $cart->id,
            'href' => '/ecommerce/cart/orderDetail/' . $cart->id
        ];
        $this->view->setTitle('Заказ №' . $cart->id);
        $this->view->page(['data' => compact('cart', 'bread')]);
    }

    function continueAction($id = 0) {
        $cart = Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->status > 1) {
            $this->url->redirect('/', 'Корзина уже оформлена');
        }
        $_SESSION['cart']['cart_id'] = $cart->id;
        $this->url->redirect('/ecommerce/cart');
    }

    function deleteAction($id = 0) {
        $cart = Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->status > 1 && $cart->status != 4) {
            $this->url->redirect('/', 'Корзина уже оформлена');
        }
        $cart->delete();
        $this->url->redirect('/ecommerce/cart/history', 'Корзина была удалена', 'success');
    }

    function refillAction($id = 0) {
        $cart = Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->status <= 1) {
            $this->url->redirect('/', 'Корзина ещё не оформлена');
        }
        $newCart = new Cart();
        $newCart->user_id = Users\User::$cur->id;
        $newCart->status = 1;
        $newCart->save();
        foreach ($cart->cartItems as $cartitem) {
            $newCart->addItem($cartitem->cci_ci_id, $cartitem->cci_ciprice_id, $cartitem->cci_count);
        }

        $newCart->save();
        $_SESSION['cart']['cart_id'] = $newCart->id;

        $this->url->redirect('/ecommerce/cart/');
    }

    function successAction() {
        $bread = [];
        $bread[] = [
            'text' => 'Каталог',
            'href' => '/ecommerce'
        ];
        $bread[] = [
            'text' => 'Корзина',
            'href' => '/ecommerce/cart'
        ];
        $bread[] = [
            'text' => 'Заказ принят',
            'href' => '/ecommerce/cart/success'
        ];
        $this->view->setTitle('Заказ принят');
        $this->view->page(['data' => compact('bread')]);
    }

    function addAction() {
        $result = new Server\Result();
        if (empty($_GET['itemOfferPriceId'])) {
            $result->success = false;
            $result->content = 'Произошла непредвиденная ошибка при добавлении товара';
            $result->send();
        }
        $price = \Ecommerce\Item\Offer\Price::get((int) $_GET['itemOfferPriceId']);
        if (!$price) {
            $result->success = false;
            $result->content = 'Такой цены не найдено';
            $result->send();
        }
        $item = $price->offer->item;

        if (!$item) {
            $result->success = false;
            $result->content = 'Такого товара не существует';
            $result->send();
        }

        $item->sales ++;
        $item->save();

        if (empty($_GET['count']))
            $count = 1;
        else
            $count = (float) $_GET['count'];

        $cart = $this->ecommerce->getCurCart();

        if (empty($this->module->config['sell_over_warehouse']) && $price->offer->warehouseCount() < $count) {
            $result->success = false;
            $result->content = 'На складе недостаточно товара! Доступно: ' . $price->offer->warehouseCount();
            $result->send();
        }

        $isset = false;
        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->item_id == $item->id && $cartItem->item_offer_price_id == $price->id) {
                $cartItem->count += $count;
                $cartItem->save();
                $isset = true;
                break;
            }
        }
        if (!$isset) {
            $cart->addItem($item->id, $price->id, $count);
        }
        $cart->calc();
        $result->successMsg = '<a href="/ecommerce/view/' . $item->id . '">' . $item->name() . '</a> добавлен <a href="/ecommerce/cart">в корзину покупок</a>!';
        $result->send();
    }

    function getcartAction() {
        $result = new Server\Result();
        ob_start();
        $this->view->widget('Ecommerce\cart');
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function delcartitemAction($cci_id = 0) {
        if (empty($_SESSION['cart']['cart_id']))
            exit('У вас нет корзины');
        $cartItem = \Ecommerce\Cart\Item::get((int) $cci_id);
        if (!$cartItem || $cartItem->cart_id != $_SESSION['cart']['cart_id']) {
            exit('Этот элемент корзины не относится к вашей корзине');
        }
        $cart = $cartItem->cart;
        $cartItem->delete();
        $cart->calc();
        $this->getcartAction();
    }

}

?>
