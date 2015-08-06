<?php

namespace Ecommerce\Cart;
class Item extends \Model {

    static $names = ['Товар в корзине', 'Товары в корзине'];

    static function table() {
        return 'catalog_cart_items';
    }

    static function index() {
        return 'cci_id';
    }

    function beforeSave() {
        if (!$this->cci_id) {
            $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 1, 'ece_info' => $this->cci_ciprice_id]);
            $event->save();
        } else {
            $cur = CartItem::get($this->cci_id);
            //var_dump($this->cci_id,Inji::app()->db->last_error,Inji::app()->db->last_query);
            if ($cur->cci_ci_id != $this->cci_ci_id) {
                $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 2, 'ece_info' => $cur->cci_ciprice_id]);
                $event->save();
                $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 1, 'ece_info' => $this->cci_ciprice_id]);
                $event->save();
            } else {
                if ($cur->cci_ciprice_id != $this->cci_ciprice_id) {

                    $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 3, 'ece_info' => $this->cci_ciprice_id]);
                    $event->save();
                }
                if ($cur->cci_count != $this->cci_count) {
                    $event = new CartEvent(['ece_cc_id' => $this->cart->cc_id, 'ece_user_id' => Inji::app()->users->cur->user_id, 'ece_ecet_id' => 4, 'ece_info' => $this->cci_ciprice_id . "|" . ($this->cci_count - $cur->cci_count)]);
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
                'cci_ci_id' => ['relation' => 'item', 'showCol' => ['type' => 'method', 'method' => 'itemNameCount'],'listGetter'=>['method'=>'itemsList','showCol'=>'combined']],
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

    static function colPrefix() {
        return 'cci_';
    }

    static function relations() {
        return [
            'item' => [
                'model' => 'Item',
                'col' => 'cci_ci_id'
            ],
            'price' => [
                'model' => 'ItemPrice',
                'col' => 'cci_ciprice_id'
            ],
            'cart' => [
                'model' => 'Cart',
                'col' => 'cci_cc_id'
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
