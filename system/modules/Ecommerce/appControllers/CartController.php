<?php

class CartController extends Controller {

    function indexAction() {
        $cart = '';
        $deliverys = \Ecommerce\Delivery::getList();
        $payTypes = \Ecommerce\PayType::getList();
        if (empty($this->module->config['packItem']['item_id']) || !$packItem = Ecommerce\Item::get($this->module->config['packItem']['item_id'])) {
            $packItem = false;
        } else {
            $packItem->price = \Ecommerce\Item\Offer\Price::get($this->module->config['packItem']['item_offer_price_id']);
        }
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Ecommerce\Cart::get($_SESSION['cart']['cart_id']);
            if (!empty($_POST)) {
                $error = false;
                if ((empty($_POST['user_phone'])) && (!Users\User::$cur->id || !Users\User::$cur->info->phone)) {
                    Msg::add('Укажите ваш номер');
                    $error = true;
                }
                if (!$error) {
                    if (!Users\User::$cur->id) {
                        $user_id = $this->Users->registration($_POST);
                        if (!$user_id) {
                            $error = true;
                        } else {
                            $user = Users\User::get($user_id);
                        }
                    } else {
                        $user = Users\User::$cur;
                    }


                    foreach ($cart->cartItems as $cartitem) {
                        $warecount = $cartitem->item->warehouseCount($cart->id);
                        if ($cartitem->count > $warecount) {
                            $error = true;
                            Msg::add('Вы заказали <b>' . $cartitem->item->name . '</b> больше чем есть на складе. на складе: <b>' . $warecount . '</b>', 'danger');
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
                    if ($user && empty($_POST['userAdds_id'])) {
                        $userAdds = New Ecommerce\UserAdds();
                        $userAdds->user_id = $user->id;
                        $userAdds->name = '';
                        foreach ($fields as $field) {
                            if (empty($_POST['userAdds']['inputs'][$field->id]) && $field->required) {
                                $error = 1;
                                Msg::add('Вы не указали: ' . $field->name);
                            }
                            if (!empty($_POST['userAdds']['inputs'][$field->id])) {
                                $userAdds->name .= htmlspecialchars($_POST['userAdds']['inputs'][$field->id]);
                            }
                        }
                    }
                    if (!$error) {

                        $cart->user_id = $user->user_id;
                        $cart->cart_status_id = 2;
                        if (!empty($_POST['user_phone'])) {
                            $cart->tel = htmlspecialchars($_POST['user_phone']);
                        } else {
                            $cart->tel = $user->user_phone;
                        }
                        $cart->fio = $_POST['user_name'];
                        $cart->email = $user->mail;
                        $cart->city = htmlspecialchars($_POST['city']);
                        $cart->street = htmlspecialchars($_POST['street']);
                        $cart->day = htmlspecialchars($_POST['day']);
                        $cart->time = htmlspecialchars($_POST['time']);
                        $cart->comment = htmlspecialchars($_POST['cc_comment']);
                        $cart->date_status = date('Y-m-d H:i:s');
                        $cart->complete_data = date('Y-m-d H:i:s');
                        $cart->paytype_id = $_POST['payType'];
                        $cart->delivery_id = $_POST['delivery'];
                        $cart->warehouse_block = 1;
                        $cart->save();
                        if (!empty($_POST['packs'])) {
                            $cart->addPacks(ceil($cart->sum / 1000));
                        }
                        \Ecommerce\Cart\Event::update(['user_id' => $user->id], ['cart_id', $cart->id]);
                        if (!$user->info->phone && $cart->tel) {
                            $user->info->phone = $cart->tel;
                            $user->info->save();
                        }
                        //unset($_SESSION['cart']['cart_id']);
                        //$this->url->redirect('/ecommerce/cart/success');
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

    function historyAction() {
        $this->view->set_title('История');
        if (!Users\User::$cur->id)
            $this->url->redirect('/', 'Вы должны войти или зарегистрироваться');

        $pages = new Pages($_GET, ['count' => Cart::getCount(['where' => ['user_id', Users\User::$cur->id]]), 'limit' => 10]);
        $carts = Cart::get_list(['where' => ['user_id', Users\User::$cur->id], 'order' => ['date', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);
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
        $this->view->page(compact('carts', 'pages', 'bread'));
    }

    function orderDetailAction($id = 0) {
        $cart = Cart::get((int) $id);
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
        $this->view->set_title('Заказ №' . $cart->id);
        $this->view->page(compact('cart', 'bread'));
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
        if (empty($_GET['item_id'])) {
            $result = [
                'image' => '/static/system/images/denied.png',
                'success' => 'Такого товара не существует',
                    //'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->sum . 'р.)'
            ];
            echo json_encode($result);
            exit();
        }

        $item = \Ecommerce\Item::get((int) $_GET['item_id']);

        if (!$item) {
            $result = [
                'image' => '/static/system/images/denied.png',
                'success' => 'Такого товара не существует',
                    //'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->sum . 'р.)'
            ];
            echo json_encode($result);
            exit();
        }

        $item->sales ++;
        $item->save();

        $offers = $item->offers(['key' => false]);
        $prices = $offers[0]->prices(['key' => false]);
        $price = $prices[0];

        if (!$price) {
            $result = [
                'image' => '/static/system/images/denied.png',
                'success' => 'Такой цены не найдено',
                    //'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->sum . 'р.)'
            ];
            echo json_encode($result);
            exit();
        }

        if (empty($_GET['count']))
            $count = 1;
        else
            $count = (float) $_GET['count'];

        $cart = $this->ecommerce->getCurCart();
        if ($item->warehouseCount() < $count) {
            $result = [
                'image' => '/static/system/images/denied.png',
                'success' => 'На складе недостаточно товара! Доступно: ' . $item->warehouseCount(),
                'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->sum . 'р.)'
            ];
            echo json_encode($result);
            exit();
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
        $cart = Ecommerce\Cart::get($cart->id);
        $result = [
            'image' => $item->image ? $item->image->path : '/static/system/images/no-image.png',
            'success' => '<a href="/ecommerce/view/' . $item->id . '">' . $item->name() . '</a> добавлен <a href="/ecommerce/cart">в корзину покупок</a>!',
            'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->sum . '.)'
        ];
        echo json_encode($result);
    }

    function updatecartitemAction() {
        if (empty($_SESSION['cart']['cart_id']))
            exit('У вас нет корзины');

        if (empty($_GET['cart_item_id']))
            exit('Не передан ид элемента корзины');

        if (empty($_GET['item_offer_price_id']))
            exit('не передан ид цены элемента корзины');

        $cartItem = \Ecommerce\Cart\Item::get((int) $_GET['cart_item_id']);

        if (!$cartItem) {
            exit('Нет такого элемента корзины');
        }

        if ($cartItem->cart_id != $_SESSION['cart']['cart_id']) {
            exit('Этот элемент корзины не относится к вашей корзине');
        }
        $count = (float) $_GET['count'];
        if ($count < 0.001)
            $count = 1;

        $item = $cartItem->item;
        $price = false;
        foreach ($item->offers as $offer) {
            if (!empty($offer->prices[(int) $_GET['item_offer_price_id']])) {
                $price = $offer->prices[(int) $_GET['item_offer_price_id']];
                break;
            }
        }
        $cartItem->count = $count;

        $cartItem->save();

        $this->getcartAction();
    }

    function getcartAction() {
        $this->view->widget('cart');
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
