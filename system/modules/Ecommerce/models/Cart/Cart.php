<?php

namespace Ecommerce;

class Cart extends \Model {

    static $objectName = 'Корзины';

    static function relations() {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ],
            'cartItems' => [
                'type' => 'many',
                'model' => 'Ecommerce\Cart\Item',
                'col' => 'cart_id',
            ],
            'events' => [
                'type' => 'many',
                'model' => 'Ecommerce\Cart\Event',
                'col' => 'cart_id',
            ],
            'status' => [
                'model' => 'Ecommerce\Cart\Status',
                'col' => 'cart_status_id'
            ],
            'delivery' => [
                'model' => 'Ecommerce\Delivery',
                'col' => 'delivery_id'
            ],
            'payType' => [
                'model' => 'Ecommerce\PayType',
                'col' => 'paytype_id'
            ],
            'userAdds' => [
                'model' => 'Ecommerce\UserAdds',
                'col' => 'useradds_id'
            ]
        ];
    }

    static $labels = [
        'user_id' => 'Пользователь',
        'sum' => 'Сумма',
        'cart_status_id' => 'Статус',
        'delivery_id' => 'Доставка',
        'comment' => 'Комментарий',
        'bonus_used' => 'Выгодные рубли',
        'complete_data' => 'Время заказа',
        'info' => 'Информация',
        'items' => 'Товары',
        'paytype_id' => 'Способ оплаты',
        'exported' => 'Экспорт',
        'warehouse_block' => 'Блокировка товаров',
    ];
    static $cols = [
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'info' => ['type' => 'select', 'source' => 'relation', 'relation' => 'userAdds'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cartItems'],
        'sum' => ['type' => 'text'],
        'warehouse_block' => ['type' => 'bool'],
        'exported' => ['type' => 'bool'],
        'comment' => ['type' => 'textarea'],
        'complete_data' => ['type' => 'dateTime'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cartItems'],
        'cart_status_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'status'],
        'delivery_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'delivery'],
        'paytype_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'payType'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'userAdds:values',
                'items',
                'sum',
                'cart_status_id',
                'exported',
                'delivery_id',
                'complete_data',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['user_id', 'cart_status_id'],
                ['paytype_id', 'delivery_id'],
                ['comment'],
                ['warehouse_block', 'complete_data'],
            //['items']
            ]
        ],
    ];

    function addPacks($count = 1) {
        $this->addItem(Inji::app()->ecommerce->modConf['packItem']['ci_id'], Inji::app()->ecommerce->modConf['packItem']['ciprice_id'], $count);
    }

    function needDelivery() {
        foreach ($this->cartItems as $cartItem) {
            if (!$cartItem->item->type) {
                continue;
            }
            if ($cartItem->item->type->cit_warehouse) {
                return true;
            }
        }
        return false;
    }

    function deliverySum() {

        if ($this->needDelivery() && $this->delivery && $this->sum < $this->delivery->cd_max_cart_price) {
            return $this->delivery->cd_price;
        }
        return 0;
    }

    function allSum() {
        return $this->sum + $this->deliverySum();
    }

    function allSumBonus() {
        return $this->sum + $this->deliverySum() - $this->bonus_used;
    }

    function itemSum() {
        return $this->sum;
    }

    function addItem($item_id, $offer_price_id, $count = 1, $final_price = 0) {
        $item = Item::get((int) $item_id);

        if (!$item) {
            return false;
        }

        $price = false;
        foreach ($item->offers as $offer) {
            if (!empty($offer->prices[(int) $offer_price_id])) {
                $price = $offer->prices[(int) $offer_price_id];
                break;
            }
        }
        if (!$price)
            return false;

        if ($count <= 0) {
            $count = 1;
        }

        $cartItem = new Cart\Item();
        $cartItem->cart_id = $this->id;
        $cartItem->item_id = $item->id;
        $cartItem->count = $count;
        $cartItem->item_offer_price_id = $price->id;
        $cartItem->final_price = $final_price ? $final_price : $price->price;
        $cartItem->save();
        return true;
    }

    function calc($save = true) {
        if (!$this->id)
            return;

        $pricesum = 0;
        $cur = new \DateTime();
        $blocks = Warehouse\Block::getList(['where' => ['cart_id', $this->id]]);
        foreach ($blocks as $block) {
            $block->delete();
        }
        $cart = Cart::get($this->id);
        foreach ($cart->cartItems as $cartItem) {
            if (!$cartItem->price) {
                continue;
            }
            $pricesum += (float) $cartItem->price->price * (float) $cartItem->count;
            if (in_array($this->cart_status_id, [0, 1, 2, 3, 6])) {
                if (in_array($this->cart_status_id, [0, 1])) {
                    $lastActive = new \DateTime($this->date_last_activ);
                    $interval = $cur->diff($lastActive);
                    if ($interval->days || $interval->h || $interval->i >= 30) {
                        continue;
                    }
                }

                $block = new Warehouse\Block();
                $block->item_offer_id = $cartItem->price->item_offer_id;
                $block->cart_id = $this->id;
                $block->count = $cartItem->count;
                $block->save();
            }
        }
        $cart->sum = $pricesum;
        if ($save) {
            $cart->save();
        }
    }

    function beforeSave() {
        //$event = false;
        if ($this->id) {
            $cur = Cart::get($this->id);
            if (!$cur) {
                return;
            }
            if ($cur->cart_status_id != $this->cart_status_id) {
                //$this->date_status = date('Y-m-d H:i:s');
                $event = new Cart\Event(['cart_id' => $this->id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 5, 'info' => $this->cart_status_id]);
                $event->save();
            }
            //$events = $cur->events(['order' => [['id', 'desc']], 'limit' => 1, 'key' => false]);
            if ($events) {
                //$event = $events[0];
            }
        }
        // if ($event)
        //$this->date_last_activ = $event->date_create;
        $this->calc(false);
    }

    function checkFormAccess($formName) {
        if ($formName == 'manage' && !in_array(Inji::app()->users->cur->user_group_id, array(3, 4))) {
            return false;
        }
        return true;
    }

}
