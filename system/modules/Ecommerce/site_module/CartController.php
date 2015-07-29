<?php

class CartController extends Controller {

    public $need = array('cc_city' => 'Город', 'cc_street' => 'Улица', 'cc_index' => 'Индекс', 'cc_fio' => 'ФИО', 'cc_tel' => 'Телефон');

    function indexAction() {
        $cart = '';
        $deliverys = Delivery::get_list();
        $payTypes = CartPayType::get_list();
        if (empty(Inji::app()->ecommerce->modConf['packItem']['ci_id']) || !$packItem = Item::get(Inji::app()->ecommerce->modConf['packItem']['ci_id'])) {
            $packItem = false;
        } else {
            $packItem->price = ItemPrice::get(Inji::app()->ecommerce->modConf['packItem']['ciprice_id']);
        }

        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Cart::get($_SESSION['cart']['cart_id']);
            if (!empty($_POST)) {
                $error = false;
                if ((empty($_POST['user_phone'])) && (!$this->users->cur->user_id || !$this->users->cur->user_phone)) {
                    $this->msg->add('Укажите ваш номер');
                    $error = true;
                }
                if (!$error) {
                    if (!$this->users->cur->user_id) {
                        $user_id = $this->Users->registration($_POST);
                        if (!$user_id) {
                            $error = true;
                        } else {
                            $user = User::get($user_id);
                        }
                    } else {
                        $user = $this->users->cur;
                    }


                    foreach ($cart->cartItems as $cartitem) {

                        $prices = $cartitem->item->prices;
                        $default = key($prices);
                        $rolePrice = 0;
                        foreach ($prices as $priceId => $itemPrice) {
                            if (!$itemPrice->type)
                                continue;
                            if (!$itemPrice->type->cipt_roles) {
                                $default = $priceId;
                                continue;
                            }
                            if ($itemPrice->type->cipt_roles && $this->users->cur->user_role_id && false !== strpos($itemPrice->type->cipt_roles, "|{$this->users->cur->user_role_id}|")) {
                                $rolePrice = $priceId;
                            }
                        }
                        $price = $prices[($rolePrice) ? $rolePrice : $default];

                        if ($price->ciprice_id != $cartitem->cci_ciprice_id) {
                            $cartitem->cci_ciprice_id = $price->ciprice_id;
                            $cartitem->save();
                            if (!$error) {
                                $this->msg->add('Один или несколько товаров были добавлены в корзину по неверной цене. Цены были пересчитаны, проверьте свой заказ', 'danger');
                            }
                            $error = true;
                        }
                        $warecount = $cartitem->item->warehouseCount((!empty($_SESSION['cart']['cart_id']) ? $_SESSION['cart']['cart_id'] : 0));
                        if ($cartitem->cci_count > $warecount) {
                            $error = true;
                            $this->msg->add('Вы заказали <b>' . $cartitem->item->ci_name . '</b> больше чем есть на складе. на складе: <b>' . $warecount . '</b>', 'danger');
                        }
                    }


                    if (empty($deliverys[$_POST['delivery']])) {
                        $error = 1;
                        $this->msg->add('Ошибка при выборе способа доставки');
                    }
                    if (empty($payTypes[$_POST['payType']])) {
                        $error = 1;
                        $this->msg->add('Ошибка при выборе способа оплаты');
                    }
                    if ($this->users->cur->user_id) {
                        $this->db->where('cub_user_id', $this->users->cur->user_id);
                        $this->db->where('cub_proof', 1);
                        $this->db->group('cub_curency');
                        $this->db->cols = '`cub_curency`, SUM(cub_sum)as `count`';
                        $cubs = $this->db->result_array($this->db->select('catalog_user_bonuses'), 'cub_curency');
                        if (!empty($cubs['ВР']['count'])) {
                            $this->db->cols = 'SUM(cc_bonus_used)as `sum`';
                            $this->db->where('cc_user_id', $this->users->cur->user_id);
                            $this->db->where('cc_status', '2,3,5', 'IN');
                            $sum = $this->db->select('catalog_carts')->fetch_assoc();
                            $vrsum = (float) ($cubs['ВР']['count'] - $sum['sum']);
                            if ($vrsum) {
                                if (round((float) $_POST['cc_bonus_used'], 2) > round($vrsum, 2)) {
                                    $error = 1;
                                    $this->msg->add('Вам недоступно такое количество выгодных рублей');
                                }
                            }
                        }
                    }
                    if (!$error) {
                        $cart->cc_user_id = $user->user_id;
                        $cart->cc_status = 2;
                        if (!empty($_POST['user_phone'])) {
                            $cart->cc_tel = htmlspecialchars($_POST['user_phone']);
                        } else {
                            $cart->cc_tel = $user->user_phone;
                        }
                        $cart->cc_fio = $user->user_name;
                        $cart->cc_email = $user->user_mail;
                        $cart->cc_city = htmlspecialchars($_POST['city']);
                        $cart->cc_street = htmlspecialchars($_POST['street']);
                        $cart->cc_day = htmlspecialchars($_POST['cc_day']);
                        $cart->cc_time = htmlspecialchars($_POST['cc_time']);
                        $cart->cc_comment = htmlspecialchars($_POST['cc_comment']);
                        $cart->cc_date_status = date('Y-m-d H:i:s');
                        $cart->cc_complete_data = date('Y-m-d H:i:s');
                        $cart->cc_pay_type = $_POST['payType'];
                        $cart->cc_delivery = $_POST['delivery'];
                        $cart->cc_warehouse_block = 1;
                        if (!empty($_POST['cc_bonus_used'])) {
                            $cart->cc_bonus_used = (float) $_POST['cc_bonus_used'];
                        }
                        $cart->save();
                        if (!empty($_POST['packs'])) {
                            $cart->addPacks(ceil($cart->cc_summ / 1000));
                        }
                        CartEvent::update(['ece_user_id' => $user->user_id], ['ece_cc_id', $cart->cc_id]);
                        if (!$user->user_phone && $cart->cc_tel) {
                            $user->user_phone = $cart->cc_tel;
                            $user->save();
                        }
                        unset($_SESSION['cart']['cart_id']);
                        $this->url->redirect('/ecommerce/cart/success');
                    }
                }
            }
        }
        $this->view->set_title('Корзина');
        $bread = [];
        $bread[] = [
            'text' => 'Каталог',
            'href' => '/ecommerce'
        ];
        $bread[] = [
            'text' => 'Корзина',
            'href' => '/ecommerce/cart'
        ];
        $this->view->page('cart', compact('cart', 'items', 'deliverys', 'payTypes', 'packItem', 'bread'));
    }

    function historyAction() {
        $this->view->set_title('История');
        if (!$this->users->cur->user_id)
            $this->url->redirect('/', 'Вы должны войти или зарегистрироваться');

        $pages = new Pages($_GET, ['count' => Cart::getCount(['where' => ['cc_user_id', $this->users->cur->user_id]]), 'limit' => 10]);
        $carts = Cart::get_list(['where' => ['cc_user_id', $this->users->cur->user_id], 'order' => ['cc_date', 'desc'], 'start' => $pages->params['start'], 'limit' => $pages->params['limit']]);
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

    function orderDetailAction($cc_id = 0) {
        $cart = Cart::get((int) $cc_id);
        if ($cart->cc_user_id != $this->users->cur->user_id) {
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
            'text' => 'Заказ: №' . $cart->cc_id,
            'href' => '/ecommerce/cart/orderDetail/'. $cart->cc_id
        ];
        $this->view->set_title('Заказ №'.$cart->cc_id);
        $this->view->page(compact('cart','bread'));
    }

    function continueAction($cc_id = 0) {
        $cart = Cart::get((int) $cc_id);
        if ($cart->cc_user_id != $this->users->cur->user_id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->cc_status > 1) {
            $this->url->redirect('/', 'Корзина уже оформлена');
        }
        $_SESSION['cart']['cart_id'] = $cart->cc_id;
        $this->url->redirect('/ecommerce/cart');
    }

    function deleteAction($cc_id = 0) {
        $cart = Cart::get((int) $cc_id);
        if ($cart->cc_user_id != $this->users->cur->user_id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->cc_status > 1 && $cart->cc_status != 4) {
            $this->url->redirect('/', 'Корзина уже оформлена');
        }
        $cart->delete();
        $this->url->redirect('/ecommerce/cart/history', 'Корзина была удалена', 'success');
    }

    function refillAction($cc_id = 0) {
        $cart = Cart::get((int) $cc_id);
        if ($cart->cc_user_id != $this->users->cur->user_id) {
            $this->url->redirect('/', 'Это не ваша корзина');
        }
        if ($cart->cc_status <= 1) {
            $this->url->redirect('/', 'Корзина ещё не оформлена');
        }
        $newCart = new Cart();
        $newCart->cc_user_id = $this->users->cur->user_id;
        $newCart->cc_status = 1;
        $newCart->save();
        foreach ($cart->cartItems as $cartitem) {
            $newCart->addItem($cartitem->cci_ci_id, $cartitem->cci_ciprice_id, $cartitem->cci_count);
        }

        $newCart->save();
        $_SESSION['cart']['cart_id'] = $newCart->cc_id;

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
        $this->view->set_title('Заказ принят');
        $this->view->page(compact('bread'));
    }

    function addAction() {
        if (empty($_GET['ci_id']))
            exit('0');

        $item = Item::get((int) $_GET['ci_id']);

        if (!$item)
            exit('0');

        $item->ci_sales ++;
        $item->save();

        $prices = $item->prices;
        $default = key($prices);
        $rolePrice = 0;
        foreach ($item->prices as $priceId => $itemPrice) {
            if (!$itemPrice->type) {
                continue;
            }
            if (!$itemPrice->type->cipt_roles) {
                $default = $priceId;
                continue;
            }
            if ($itemPrice->type->cipt_roles && $this->users->cur->user_role_id && false !== strpos($itemPrice->type->cipt_roles, "|{$this->users->cur->user_role_id}|")) {
                $rolePrice = $priceId;
            }
        }
        $price = $item->prices[($rolePrice) ? $rolePrice : $default];

        if (!$price)
            exit('0');

        if (empty($_GET['count']))
            $count = 1;
        else
            $count = (float) $_GET['count'];

        $cart = $this->ecommerce->getCurCart();
        if ($item->warehouseCount() < $count) {
            $result = [
                'image' => '/static/images/denied.png',
                'success' => 'На складе недостаточно товара! Доступно: ' . $item->warehouseCount(),
                'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->cc_summ . 'р.)'
            ];
            echo json_encode($result);
            exit();
        }
        $isset = false;
        foreach ($cart->cartItems as $cartItem) {
            if ($cartItem->cci_ci_id == $item->ci_id && $cartItem->cci_ciprice_id == $price->ciprice_id) {
                $cartItem->cci_count += $count;
                $cartItem->save();
                $isset = true;
                break;
            }
        }
        if (!$isset) {
            $cart->addItem($item->ci_id, $price->ciprice_id, $count);
        }
        $cart->calc();
        $result = [
            'image' => 'http://opencart.test/image/cache/no_image-47x47.png',
            'success' => '<a href="/ecommerce/view/' . $item->ci_id . '">' . $item->ci_name . '</a> добавлен <a href="/ecommerce/cart">в корзину покупок</a>!',
            'total' => 'Товаров ' . count($cart->cartItems) . ' (' . $cart->cc_summ . '.)'
        ];
        echo json_encode($result);
        //$this->getcartAction();
    }

    function updatecartitemAction() {
        if (empty($_SESSION['cart']['cart_id']))
            exit('1');

        if (empty($_GET['cci_id']))
            exit('2');

        $cartItem = CartItem::get((int) $_GET['cci_id']);

        if (!$cartItem) {
            exit('3');
        }

        if ($cartItem->cart->cc_id != $_SESSION['cart']['cart_id']) {
            exit('3');
        }
        $count = (float) $_GET['count'];
        if ($count < 0.001)
            $count = 1;

        $item = $cartItem->item;
        $prices = $item->prices;
        $default = key($prices);
        $rolePrice = 0;
        foreach ($item->prices as $priceId => $itemPrice) {
            if (!$itemPrice->type) {
                continue;
            }
            if (!$itemPrice->type->cipt_roles) {
                $default = $priceId;
                continue;
            }
            if ($itemPrice->type->cipt_roles && $this->users->cur->user_role_id && false !== strpos($itemPrice->type->cipt_roles, "|{$this->users->cur->user_role_id}|")) {
                $rolePrice = $priceId;
            }
        }
        $price = $item->prices[($rolePrice) ? $rolePrice : $default];

        $cartItem->cci_ciprice_id = $price->ciprice_id;
        $cartItem->cci_count = $count;

        $cartItem->save();
        $cartItem->cart->calc();

        $this->getcartAction();
    }

    function getcartAction() {
        $cart = $this->ecommerce->getCurCart();
        $this->view->widget('cart');
    }

    function delcartitemAction($cci_id = 0) {
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Cart::get((int) $_SESSION['cart']['cart_id']);
        }
        if (!empty($cart->cartItems[(int) $cci_id])) {
            $cart->cartItems[(int) $cci_id]->delete();
        }
        $cart->calc();
        $this->getcartAction();
    }

}

?>
