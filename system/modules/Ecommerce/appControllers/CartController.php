<?php

class CartController extends Controller
{
    function indexAction()
    {
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
                if ($deliverys && empty($deliverys[$_POST['delivery']])) {
                    $error = 1;
                    Msg::add('Ошибка при выборе способа доставки');
                } elseif ($deliverys && !empty($deliverys[$_POST['delivery']])) {
                    $delivery = $deliverys[$_POST['delivery']];
                } else {
                    $delivery = null;
                }
                if ($payTypes && empty($payTypes[$_POST['payType']])) {
                    $error = 1;
                    Msg::add('Ошибка при выборе способа оплаты');
                } elseif ($payTypes && !empty($payTypes[$_POST['payType']])) {
                    $payType = $payTypes[$_POST['payType']];
                } else {
                    $payType = null;
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
                    $cart->paytype_id = $payType ? $payType->id : 0;
                    $cart->delivery_id = $delivery ? $delivery->id : 0;
                    $cart->warehouse_block = 1;
                    $cart->save();

                    $cart = \Ecommerce\Cart::get($cart->id);
                    foreach ($cart->cartItems as $cartItem) {
                        $cartItem->discount = $cartItem->discount();
                        $cartItem->final_price = $cartItem->price->price - $cartItem->discount;
                        $cartItem->save();
                    }
                    unset($_SESSION['cart']['cart_id']);
                    if (!empty(\App::$cur->ecommerce->config['notify_mail'])) {
                        $text = 'Перейдите в админ панель чтобы просмотреть новый заказ <a href = "http://' . idn_to_utf8(INJI_DOMAIN_NAME) . '/admin/ecommerce/Cart">Админ панель</a>';
                        $title = 'Новый заказ в интернет магазине на сайте ' . idn_to_utf8(INJI_DOMAIN_NAME);
                        \Tools::sendMail('noreply@' . idn_to_utf8(INJI_DOMAIN_NAME), \App::$cur->ecommerce->config['notify_mail'], $title, $text);
                    }
                    if ($this->notifications) {
                        $notification = new Notifications\Notification();
                        $notification->name = 'Новый заказ в интернет магазине на сайте ' . idn_to_utf8(INJI_DOMAIN_NAME);
                        $notification->text = 'Перейдите в админ панель чтобы просмотреть новый заказ';
                        $notification->chanel_id = $this->notifications->getChanel('Ecommerce-orders')->id;
                        $notification->save();
                    }
                    if ($payType && $payType->merchants && $this->money) {
                        $sums = [];
                        foreach ($cart->cartItems as $cartItem) {
                            $currency_id = $cartItem->price->currency ? $cartItem->price->currency->id : \App::$cur->ecommerce->config['defaultCurrency'];
                            if (empty($sums[$currency_id])) {
                                $sums[$currency_id] = $cartItem->final_price * $cartItem->count;
                            } else {
                                $sums[$currency_id] += $cartItem->final_price * $cartItem->count;
                            }
                        }
                        foreach ($sums as $currency_id => $sum) {
                            if (!$currency_id) {
                                continue;
                            }
                            $pay = new Money\Pay([
                                'data' => $cart->id,
                                'currency_id' => $currency_id,
                                'user_id' => \Users\User::$cur->id,
                                'sum' => $cart->finalSum(),
                                'description' => 'Оплата заказа №' . $cart->id . ' в онлайн-магазине',
                                'type' => 'pay',
                                'pay_status_id' => 1,
                                'callback_module' => 'Ecommerce',
                                'callback_method' => 'cartPayRecive'
                            ]);
                            $pay->save();
                        }
                        Tools::redirect('/money/merchants/pay/');
                    } else {
                        Tools::redirect('/ecommerce/cart/success');
                    }
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

    function orderDetailAction($id = 0)
    {
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

    function continueAction($id = 0)
    {
        $cart = \Ecommerce\Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            Tools::redirect('/', 'Это не ваша корзина');
        }
        if ($cart->cart_status_id > 1) {
            Tools::redirect('/', 'Корзина уже оформлена');
        }
        $_SESSION['cart']['cart_id'] = $cart->id;
        Tools::redirect('/ecommerce/cart');
    }

    function deleteAction($id = 0)
    {
        $cart = \Ecommerce\Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            Tools::redirect('/', 'Это не ваша корзина');
        }
        if ($cart->cart_status_id > 1) {
            Tools::redirect('/', 'Корзина уже оформлена');
        }
        if (!empty($_SESSION['cart']['cart_id']) && $_SESSION['cart']['cart_id'] == $cart->id) {
            unset($_SESSION['cart']['cart_id']);
        }
        $cart->delete();
        Tools::redirect('/users/cabinet/ecommerceOrdersHistory', 'Корзина была удалена', 'success');
    }

    function refillAction($id = 0)
    {
        $cart = \Ecommerce\Cart::get((int) $id);
        if ($cart->user_id != Users\User::$cur->id) {
            Tools::redirect('/', 'Это не ваша корзина');
        }
        if (!empty($_SESSION['cart']['cart_id'])) {
            unset($_SESSION['cart']['cart_id']);
        }
        $newCart = $this->ecommerce->getCurCart();
        foreach ($cart->cartItems as $cartitem) {
            $newCart->addItem($cartitem->item_id, $cartitem->item_offer_price_id, $cartitem->count);
        }

        $newCart->save();

        Tools::redirect('/ecommerce/cart/');
    }

    function successAction()
    {
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

    function addAction()
    {
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
        $result->successMsg = '<a href="/ecommerce/view/' . $item->id . '">' . $item->name() . ($price->offer->name() ? ' (' . $price->offer->name() . ')' : '') . '</a> добавлен <a href="/ecommerce/cart">в корзину покупок</a>!';
        $result->send();
    }

    function getcartAction()
    {
        $result = new Server\Result();
        ob_start();
        $this->view->widget('Ecommerce\cart');
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function delcartitemAction($cci_id = 0)
    {
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
