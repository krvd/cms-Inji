<?php

namespace Ecommerce;

class Cart extends \Model {

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
            ]
        ];
    }

    static $names = ['Корзина', 'Корзины', 'Корзин'];
    static $labels = [
        'cc_user_id' => 'Пользователь',
        'sum' => 'Сумма',
        'cc_status' => 'Статус',
        'cc_city' => 'Город',
        'cc_street' => 'Улица',
        'cc_day' => 'День',
        'cc_time' => 'Время',
        'cc_fio' => 'ФИО',
        'cc_tel' => 'Телефон',
        'cc_delivery' => 'Доставка',
        'cc_comment' => 'Комментарий',
        'cc_bonus_used' => 'Выгодные рубли',
        'cc_tel' => 'Телефон',
        'cc_date' => 'Время заказа',
        'cc_complete_data' => 'Время заказа',
        'info' => 'Информация',
        'items' => 'Товары',
        'cc_pay_type' => 'Способ оплаты',
        'cc_exported' => '1c',
        'cc_warehouse_block' => 'Блокировка товаров'
    ];
    static $dataTable = [
        'filters' => ['col' => 'cc_status', 'relation' => 'status'],
        'order' => ['col' => 'cc_complete_data', 'type' => 'desc'],
        'cols' => [
            'info' => ['popUpEditForm' => 'infoEdit', 'showCol' => 'cc_fio'],
            'items' => ['relation' => 'cartItems'],
            'sum' => ['popUpEditForm' => 'sum', 'showCol' => 'sum'],
            'cc_status' => ['widget' => 'statusCart'],
            'cc_exported' => [],
            'cc_delivery' => ['relation' => 'delivery', 'showCol' => 'cd_name'],
            'cc_complete_data' => [],
        ]
    ];
    static $forms = [
        'manage' => [
            'options' => [
                'cc_user_id' => ['relation' => 'user', 'showCol' => 'user_name'],
                'cc_status' => ['relation' => 'status', 'showCol' => 'ccs_name'],
                'cc_tel' => 'text',
                'cc_fio' => 'text',
                'cc_city' => 'text',
                'cc_street' => 'text',
                'cc_day' => 'text',
                'cc_time' => 'text',
                'cc_complete_data' => 'datetime',
                'cc_comment' => 'textarea',
                'cc_bonus_used' => 'text',
                'cc_warehouse_block' => 'checkbox',
                'cc_pay_type' => ['relation' => 'payType', 'showCol' => 'cpt_name'],
                'cc_delivery' => ['relation' => 'delivery', 'showCol' => 'cd_name'],
                'items' => ['relation' => 'cartItems', 'form' => 'inlineEdit']
            ],
            'relations' => [
                'cci_ci_id' => [
                    'col' => 'cci_ciprice_id',
                    'model' => 'Item',
                    'relation' => 'prices'
                ]
            ],
            'helpers' => [
                'itemSum' => [
                    'text' => 'Сумма товаров',
                    'value' => [
                        'method' => 'itemSum'
                    ]
                ],
                'deliverySum' => [
                    'text' => 'Стоимость доставки',
                    'value' => [
                        'method' => 'deliverySum'
                    ]
                ],
                'allSum' => [
                    'text' => 'Итоговая сумма',
                    'value' => [
                        'method' => 'allSum'
                    ]
                ],
                'allSumBonus' => [
                    'text' => 'Итоговая сумма с учетом выгодных рублей',
                    'value' => [
                        'method' => 'allSumBonus'
                    ]
                ],
            ],
            'map' => [
                ['cc_user_id', 'cc_status'],
                ['cc_fio', 'cc_tel'],
                ['cc_city', 'cc_street'],
                ['cc_day', 'cc_time'],
                ['cc_bonus_used', 'cc_delivery'],
                ['cc_pay_type', 'cc_comment'],
                ['cc_warehouse_block', 'cc_complete_data'],
                ['items']
            ]
        ],
        'infoEdit' => [
            'options' => [
                'cc_tel' => 'text',
                'cc_fio' => 'text',
                'cc_city' => 'text',
                'cc_street' => 'text',
                'cc_day' => 'text',
                'cc_time' => 'text',
                'cc_comment' => 'textarea',
            ],
            'helpers' => [
                'itemSum' => [
                    'text' => 'Сумма товаров',
                    'value' => [
                        'method' => 'itemSum'
                    ]
                ],
                'deliverySum' => [
                    'text' => 'Стоимость доставки',
                    'value' => [
                        'method' => 'deliverySum'
                    ]
                ],
                'allSum' => [
                    'text' => 'Итоговая сумма',
                    'value' => [
                        'method' => 'allSum'
                    ]
                ],
                'allSumBonus' => [
                    'text' => 'Итоговая сумма с учетом выгодных рублей',
                    'value' => [
                        'method' => 'allSumBonus'
                    ]
                ],
            ],
            'map' => [
                ['cc_fio', 'cc_tel'],
                ['cc_city', 'cc_street'],
                ['cc_day', 'cc_time'],
                ['cc_comment']
            ]
        ],
        'sum' => [
            'options' => [
                'cc_bonus_used' => 'text',
                'cc_delivery' => ['relation' => 'delivery', 'showCol' => 'cd_name'],
            ],
            'helpers' => [
                'itemSum' => [
                    'text' => 'Сумма товаров',
                    'value' => [
                        'method' => 'itemSum'
                    ]
                ],
                'deliverySum' => [
                    'text' => 'Стоимость доставки',
                    'value' => [
                        'method' => 'deliverySum'
                    ]
                ],
                'allSum' => [
                    'text' => 'Итоговая сумма',
                    'value' => [
                        'method' => 'allSum'
                    ]
                ],
                'allSumBonus' => [
                    'text' => 'Итоговая сумма с учетом выгодных рублей',
                    'value' => [
                        'method' => 'allSumBonus'
                    ]
                ],
            ],
            'map' => [
                ['cc_bonus_used', 'cc_delivery'],
            ]
        ],
        'itemsEdit' => [
            'options' => [
                'items' => ['relation' => 'cartItems']
            ],
            'map' => [
                ['items']
            ]
        ]
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
        return $this->sum + $this->deliverySum() - $this->cc_bonus_used;
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
        if ($this->cc_id) {
            $cur = Cart::get($this->cc_id);
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
