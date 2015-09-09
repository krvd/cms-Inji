<?php

namespace Ecommerce\Cart;

class Item extends \Model {

    function beforeSave() {
        if (!$this->id) {
            $event = new Event(['cart_id' => $this->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 1, 'info' => $this->item_offer_price_id]);
            $event->save();
        } else {
            $cur = Item::get($this->id);
            if ($cur->item_id != $this->item_id) {
                $event = new Event(['cart_id' => $this->cart->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 2, 'info' => $cur->item_offer_price_id]);
                $event->save();
                $event = new Event(['cart_id' => $this->cart->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 1, 'info' => $this->item_offer_price_id]);
                $event->save();
            } else {
                if ($cur->item_offer_price_id != $this->item_offer_price_id) {
                    $event = new Event(['cart_id' => $this->cart->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 3, 'info' => $this->item_offer_price_id]);
                    $event->save();
                }
                if ($cur->count != $this->count) {
                    $event = new Event(['cart_id' => $this->cart->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 4, 'info' => $this->item_offer_price_id . "|" . ($this->count - $cur->count)]);
                    $event->save();
                }
            }
        }
    }

    function afterSave() {
        $this->cart->calc();
    }

    function afterDelete() {
        $event = new Event(['cart_id' => $this->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 2, 'info' => $this->item_offer_price_id]);
        $event->save();
        $this->cart->calc();
    }

    static $labels = [
        'item_id' => 'Товар',
        'item_offer_price_id' => 'Цена',
        'count' => 'Количество',
        'cart_id' => 'Корзина'
    ];
    static $cols = [
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'cart_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cart'],
        'item_offer_price_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'price', 'showCol' => 'price'],
        'count' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Покупки',
            'cols' => [
                'item_id',
                'item_offer_price_id',
                'count',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'relations' => [
                'item_id' => [
                    'col' => 'item_offer_price_id',
                    'model' => 'Item',
                    'relation' => 'prices'
                ]
            ],
            'map' => [
                ['cart_id', 'item_id'],
                ['item_offer_price_id', 'count'],
            ]
        ]
    ];

    static function relations() {
        return [
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ],
            'price' => [
                'model' => 'Ecommerce\Item\Offer\Price',
                'col' => 'item_offer_price_id'
            ],
            'cart' => [
                'model' => 'Ecommerce\Cart',
                'col' => 'cart_id'
            ]
        ];
    }

}
