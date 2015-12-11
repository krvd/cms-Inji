<?php

/**
 * Cart
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Cart extends \Model
{
    static $objectName = 'Корзины';

    static function relations()
    {
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
            ],
            'pays' => [
                'type' => 'many',
                'model' => 'Money\Pay',
                'col' => 'data'
            ]
        ];
    }

    function beforeDelete()
    {
        foreach ($this->cartItems as $cartItem) {
            $cartItem->delete();
        }
        foreach ($this->infos as $info) {
            $info->delete();
        }
        foreach ($this->extras as $extra) {
            $extra->delete();
        }
        foreach ($this->events as $event) {
            $event->delete();
        }
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
        'payed' => 'Оплачен',
        'exported' => 'Выгружено',
        'warehouse_block' => 'Блокировка товаров',
        'extra' => 'Дополнительно',
        'card_item_id' => 'Дисконтная карта',
        'info' => 'Информация',
        'pay' => 'Счета оплаты',
        'sums' => 'Суммы',
    ];
    static $cols = [
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'info' => ['type' => 'dataManager', 'relation' => 'infos'],
        'items' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cartItems'],
        'sum' => ['type' => 'text'],
        'warehouse_block' => ['type' => 'bool'],
        'payed' => ['type' => 'bool'],
        'exported' => ['type' => 'bool'],
        'comment' => ['type' => 'textarea'],
        'complete_data' => ['type' => 'dateTime'],
        'items' => ['type' => 'dataManager', 'relation' => 'cartItems'],
        'cart_status_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'status'],
        'delivery_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'delivery'],
        'paytype_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'payType'],
        'card_item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'card'],
        'extra' => ['type' => 'dataManager', 'relation' => 'extras'],
        'pay' => ['type' => 'dataManager', 'relation' => 'pays'],
        'sums' => [
            'type' => 'void',
            'view' => [
                'type' => 'widget',
                'widget' => 'Ecommerce\adminSums',
            ],
        ],
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'info',
                'items',
                'extra',
                'sums',
                'cart_status_id',
                'delivery_id',
                'payed',
                'pay',
                'complete_data',
            ],
            'sortable' => [
                'cart_status_id',
                'delivery_id',
                'payed',
                'complete_data',
            ],
            'filters' => [
                'cart_status_id',
                'payed',
                'delivery_id',
                'payed',
                'complete_data',
            ],
            'preSort' => [
                'complete_data' => 'desc'
            ],
            'rowButtonsWidget' => 'Ecommerce\cart/adminButtons'
        ]
    ];
    static $forms = [
        'manager' => [
            'inputs' => [
                'userSearch' => [
                    'type' => 'search',
                    'source' => 'relation',
                    'relation' => 'user',
                    'label' => 'Покупатель',
                    'cols' => [
                        'info:first_name',
                        'info:last_name',
                        'info:middle_name',
                        'mail'
                    ],
                    'col' => 'user_id',
                    'required' => true,
                ],
                'cardSearch' => [
                    'type' => 'search',
                    'source' => 'relation',
                    'relation' => 'card',
                    'label' => 'Дисконтная карта',
                    'cols' => [
                        'code',
                        'user:info:first_name',
                        'user:info:last_name',
                        'user:info:middle_name',
                        'user:mail'
                    ],
                    'col' => 'card_item_id',
                ],
            ],
            'map' => [
                ['userSearch', 'cart_status_id'],
                ['paytype_id', 'delivery_id'],
                ['cardSearch', 'comment'],
                ['warehouse_block', 'complete_data'],
                ['payed'],
                ['items'],
                ['extra'],
                ['pay']
            ]
        ],
    ];

    function addPacks($count = 1)
    {
        $this->addItem(Inji::app()->ecommerce->modConf['packItem']['ci_id'], Inji::app()->ecommerce->modConf['packItem']['ciprice_id'], $count);
    }

    function needDelivery()
    {
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

    function deliverySum()
    {

        if ($this->needDelivery() && $this->delivery && $this->sum < $this->delivery->cd_max_cart_price) {
            return $this->delivery->cd_price;
        }
        return 0;
    }

    function discountSun()
    {
        $discountSum = 0;
        foreach ($this->cartItems as $cartItem) {
            $discountSum += $cartItem->discount();
        }
        return $discountSum;
    }

    function finalSum()
    {
        return $this->sum + $this->deliverySum() - $this->disountSum();
    }

    function itemSum()
    {
        return $this->sum;
    }

    function addItem($item_id, $offer_price_id, $count = 1, $final_price = 0)
    {
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

    function calc($save = true)
    {
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

    function beforeSave()
    {
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
