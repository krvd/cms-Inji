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
        $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 2, 'ece_info' => $this->cci_ciprice_id]);
        $event->save();
        $this->cart->calc();
    }

    static $labels = [
        'cci_ci_id' => 'Товар',
        'cci_ciprice_id' => 'Цена',
        'cci_count' => 'Количество',
        'cci_cc_id' => 'Корзина'
    ];
    static $dataTable = [
        'cols' => [
            'cci_ci_id' => ['relation' => 'item', 'showCol' => 'ci_name'],
            'cci_ciprice_id' => ['relation' => 'price', 'showCol' => 'ciprice_price'],
            'cci_count' => [],
        ],
        'helpers' => [
            'totalSum' => [
                'type' => 'sum',
                'value' => [
                    [
                        'relation' => 'price',
                        'col' => 'ciprice_price'
                    ],
                    [
                        'col' => 'cci_count'
                    ]
                ],
                'html' => '<big>Сумма товаров: <b class = "helperValue"></b></big>'
            ]
        ]
    ];
    static $forms = [
        'inlineEdit' => [
            'options' => [
                'cci_ci_id' => ['relation' => 'item', 'showCol' => ['type' => 'method', 'method' => 'itemNameCount'], 'listGetter' => ['method' => 'itemsList', 'showCol' => 'combined']],
                'cci_ciprice_id' => ['relation' => 'price', 'showCol' => 'ciprice_price'],
                'cci_count' => [],
            ],
            'relations' => [
                'cci_ci_id' => [
                    'col' => 'cci_ciprice_id',
                    'model' => 'Item',
                    'relation' => 'prices'
                ]
            ],
        ],
        'manage' => [
            'options' => [
                'cci_cc_id' => ['relation' => 'cart', 'showCol' => 'cc_id'],
                'cci_ci_id' => ['relation' => 'item', 'showCol' => 'ci_name'],
                'cci_ciprice_id' => ['relation' => 'price', 'showCol' => 'ciprice_price'],
                'cci_count' => 'text',
            ],
            'relations' => [
                'cci_ci_id' => [
                    'col' => 'cci_ciprice_id',
                    'model' => 'Item',
                    'relation' => 'prices'
                ]
            ],
            'map' => [
                ['cci_cc_id', 'cci_ci_id'],
                ['cci_ciprice_id', 'cci_count'],
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

    function checkFormAccess($formName) {
        if ($formName == 'manage' && !in_array(Inji::app()->users->cur->user_group_id, array(3, 4))) {
            return false;
        }
        return true;
    }

}
