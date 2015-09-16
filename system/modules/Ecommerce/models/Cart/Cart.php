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
            'infos' => [
                'type' => 'many',
                'model' => 'Ecommerce\Cart\Info',
                'col' => 'cart_id'
            ],
            'extras' => [
                'type' => 'many',
                'model' => 'Ecommerce\Cart\Extra',
                'col' => 'cart_id'
            ],
            'card' => [
                'model' => 'Ecommerce\Card\Item',
                'col' => 'card_item_id'
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
        'exported' => 'Выгружено',
        'warehouse_block' => 'Блокировка товаров',
        'extra' => 'Дополнительно',
        'card_item_id' => 'Дисконтная карта',
        'info' => 'Информация',
    ];
    static $cols = [
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'info' => ['type' => 'dataManager', 'relation' => 'infos'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cartItems'],
        'sum' => ['type' => 'text'],
        'warehouse_block' => ['type' => 'bool'],
        'exported' => ['type' => 'bool'],
        'comment' => ['type' => 'textarea'],
        'complete_data' => ['type' => 'dateTime'],
        'items' => ['type' => 'dataManager', 'relation' => 'cartItems'],
        'cart_status_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'status'],
        'delivery_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'delivery'],
        'paytype_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'payType'],
        'card_item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'card'],
        'extra' => ['type' => 'dataManager', 'relation' => 'extras'],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'info',
                'items',
                'extra',
                'sum',
                'cart_status_id',
                'delivery_id',
                'complete_data',
            ],
            'sortable' => [
                'sum',
                'cart_status_id',
                'delivery_id',
                'complete_data',
            ],
            'filters' => [
                'sum',
                'cart_status_id',
                'delivery_id',
                'complete_data',
            ],
            'preSort' => [
                'complete_data' => 'desc'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['user_id', 'cart_status_id'],
                ['paytype_id', 'delivery_id'],
                ['card_item_id', 'comment'],
                ['warehouse_block', 'complete_data'],
                ['items'],
                ['extra']
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
        if (!$this->id) {
            return;
        }

        $this->sum = 0;
        $cart = Cart::get($this->id);
        foreach ($cart->cartItems as $cartItem) {
            if (!$cartItem->price) {
                continue;
            }
            $this->sum += (float) ($cartItem->price->price - $cartItem->discount()) * (float) $cartItem->count;
        }
        foreach ($cart->extras as $extra) {
            $this->sum += $extra->price * $extra->count;
        }
        if ($save) {
            $this->save();
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
                $this->date_status = date('Y-m-d H:i:s');
                if ($this->cart_status_id == 5 && $cur->cart_status_id == 3) {
                    if ($this->card) {
                        $sum = 0;
                        foreach ($this->cartItems as $cartItem) {
                            $sum += ($cartItem->price->price - $cartItem->discont()) * $cartItem->count;
                        }
                        $cardItemHistory = new Card\Item\History();
                        $cardItemHistory->amount = $sum;
                        $cardItemHistory->card_item_id = $this->card_item_id;
                        $cardItemHistory->save();
                        $this->card->sum += $sum;
                        $this->card->save();
                    }
                }
                $event = new Cart\Event(['cart_id' => $this->id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 5, 'info' => $this->cart_status_id]);
                $event->save();
            }
            //$events = $cur->events(['order' => [['id', 'desc']], 'limit' => 1, 'key' => false]);
            //if ($events) {
            //$event = $events[0];
            //}
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
