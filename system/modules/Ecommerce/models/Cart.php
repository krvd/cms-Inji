<?php

/**
 * Cart model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class Cart extends Model
{

    static function colPrefix()
    {
        return 'cc_';
    }

    static function table()
    {
        return 'catalog_carts';
    }

    static function index()
    {
        return 'cc_id';
    }

    static function relations()
    {
        return [
            'user' => [
                'model' => 'User',
                'col' => 'cc_user_id'
            ],
            'cartItems' => [
                'type' => 'many',
                'model' => 'CartItem',
                'col' => 'cci_cc_id',
            ],
            'events' => [
                'type' => 'many',
                'model' => 'CartEvent',
                'col' => 'ece_cc_id',
            ],
            'status' => [
                'model' => 'CartStatus',
                'col' => 'cc_status'
            ],
            'delivery' => [
                'model' => 'Delivery',
                'col' => 'cc_delivery'
            ],
            'payType' => [
                'model' => 'CartPayType',
                'col' => 'cc_pay_type'
            ]
        ];
    }

    static $names = ['Корзина', 'Корзины', 'Корзин'];
    static $labels = [
        'cc_user_id' => 'Пользователь',
        'cc_summ' => 'Сумма',
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
        'cc_warehouse_block'=>'Блокировка товаров'
    ];
    static $dataTable = [
        'filters' => ['col' => 'cc_status', 'relation' => 'status'],
        'order' => ['col' => 'cc_complete_data', 'type' => 'desc'],
        'cols' => [
            'info' => ['popUpEditForm' => 'infoEdit', 'showCol' => 'cc_fio'],
            'items' => ['relation' => 'cartItems'],
            'cc_summ' => ['popUpEditForm' => 'sum', 'showCol' => 'cc_summ'],
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
                ['cc_warehouse_block','cc_complete_data'],
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

    function addCard()
    {
        $this->cc_card_buy = 1;
        $this->save();
        $this->user->user_role_id = 4;
        $this->user->save();
        $this->addItem(Inji::app()->ecommerce->modConf['cardItem']['ci_id'], Inji::app()->ecommerce->modConf['cardItem']['ciprice_id']);
    }

    function addPacks($count = 1)
    {
        $this->addItem(Inji::app()->ecommerce->modConf['packItem']['ci_id'], Inji::app()->ecommerce->modConf['packItem']['ciprice_id'], $count);
    }

    function needDelivery()
    {
        foreach ($this->cartItems as $cartItem) {
            if(!$cartItem->item->type){
                continue;
            }
            if ($cartItem->item->type->cit_warehouse) {
                return true;
            }
        }
        return false;
    }

    function deliverySum()
    {

        if ($this->needDelivery() && $this->delivery && $this->cc_summ < $this->delivery->cd_max_cart_price) {
            return $this->delivery->cd_price;
        }
        return 0;
    }

    function allSum()
    {
        return $this->cc_summ + $this->deliverySum();
    }

    function allSumBonus()
    {
        return $this->cc_summ + $this->deliverySum() - $this->cc_bonus_used;
    }

    function itemSum()
    {
        return $this->cc_summ;
    }

    function addItem($ci_id, $ciprice_id, $count = 1, $final_price = 0)
    {
        $item = Item::get((int) $ci_id);

        if (!$item)
            return false;

        if (empty($item->prices[(int) $ciprice_id]))
            return false;

        if ($count <= 0) {
            $count = 1;
        }

        $cartItem = new CartItem();
        $cartItem->cci_cc_id = $this->cc_id;
        $cartItem->cci_ci_id = $item->ci_id;
        $cartItem->cci_count = $count;
        $cartItem->cci_ciprice_id = $item->prices[(int) $ciprice_id]->ciprice_id;
        $cartItem->cci_final_price = $final_price ? $final_price : $item->prices[(int) $ciprice_id]->ciprice_price;
        $cartItem->save();
        return true;
    }

    function calc($save = true)
    {
        if (!$this->cc_id)
            return;
        $cart = Cart::get($this->cc_id);
        if (!$cart) {
            return;
        }
        $pricesumm = 0;
        $cur = new DateTime();
        $blocks = WarehouseBlock::get_list(['where' => ['ewb_cc_id', $this->cc_id]]);
        foreach ($blocks as $block) {
            $block->delete();
        }
        
        foreach ($cart->cartItems as $cartItem) {
            
            if (!$cartItem->price) {
                continue;
            }
            
            $pricesumm += (float) $cartItem->price->ciprice_price * (float) $cartItem->cci_count;
            if (!empty(Inji::app()->ecommerce->modConf['cardItem']) && $cartItem->cci_ci_id == Inji::app()->ecommerce->modConf['cardItem']['ci_id'] && $cartItem->cci_ciprice_id = Inji::app()->ecommerce->modConf['cardItem']['ciprice_id']) {
                $this->cc_card_buy = 1;
                $this->user->user_role_id = 4;
                $this->user->save();
            }

            if (in_array($this->cc_status, [0, 1, 2, 3, 6])) {
                if (in_array($this->cc_status, [0, 1])) {
                    
                    $lastActive = new DateTime($this->cc_date_last_activ);
                    $interval = $cur->diff($lastActive);
                    if ($interval->days || $interval->h || $interval->i >= 30) {
                        continue;
                    }
                }
                
                $block = WarehouseBlock::get([['ewb_ci_id', $cartItem->cci_ci_id], ['ewb_cc_id', $this->cc_id]]);
                if (!$block) {
                    
                    $block = new WarehouseBlock();
                    $block->ewb_ci_id = $cartItem->cci_ci_id;
                    $block->ewb_cc_id = $this->cc_id;
                    $block->ewb_count = $cartItem->cci_count;
                    $block->save();
                } elseif ($block->ewb_count != $cartItem->cci_count) {
                    $block->ewb_count = $cartItem->cci_count;
                    $block->save();
                }
            }
        }
        $cart->cc_summ = $pricesumm;
        if ($save)
            $cart->save();
    }

    function beforeSave()
    {
        $event = false;
        if ($this->cc_id) {
            $cur = Cart::get($this->cc_id);
            if (!$cur) {
                return;
            }
            if ($cur->cc_status != $this->cc_status) {
                $this->cc_date_status = date('Y-m-d H:i:s');
                $event = new CartEvent(['ece_cc_id' => $this->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 5, 'ece_info' => $this->cc_status]);
                $event->save();
            }
            $events = $cur->events(['order' => [['ece_id', 'desc']], 'limit' => 1, 'key' => false]);
            if ($events) {
                $event = $events[0];
            }
        }
        if ($event)
            $this->cc_date_last_activ = $event->ece_date;
        $this->calc(false);
    }

    function checkFormAccess($formName)
    {
        if ($formName == 'manage' && !in_array(Inji::app()->users->cur->user_group_id, array(3, 4))) {
            return false;
        }
        return true;
    }

}
