<?php

/**
 * Cart item
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Cart;

class Item extends \Model
{
    public function beforeSave()
    {
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

    public function afterSave()
    {
        $block = \Ecommerce\Warehouse\Block::get([['cart_id', $this->cart->id], ['item_offer_id', $this->price->item_offer_id]]);
        if (in_array($this->cart_status_id, [0, 1, 2, 3, 6])) {
            if (in_array($this->cart_status_id, [0, 1])) {
                $cur = new \DateTime();
                $lastActive = new \DateTime($this->date_last_activ);
                $interval = $cur->diff($lastActive);
                if ($interval->days || $interval->h || $interval->i >= 30) {
                    if ($block) {
                        $block->delete();
                    }
                    $this->cart->save();
                    return;
                }
            }
            $block = \Ecommerce\Warehouse\Block::get([['cart_id', $this->cart_id], ['item_offer_id', $this->price->item_offer_id]]);
            if ($block) {
                $block->count = $this->count;
                $block->save();
            } else {
                $block = new \Ecommerce\Warehouse\Block();
                $block->item_offer_id = $this->price->item_offer_id;
                $block->cart_id = $this->cart_id;
                $block->count = $this->count;
                $block->save();
            }
        } elseif ($block) {
            $block->delete();
        }
        $this->cart->save();
    }

    public function afterDelete()
    {
        $event = new Event(['cart_id' => $this->cart_id, 'user_id' => \Users\User::$cur->id, 'cart_event_type_id' => 2, 'info' => $this->item_offer_price_id]);
        $event->save();
        $block = \Ecommerce\Warehouse\Block::get([['cart_id', $this->cart->id], ['item_offer_id', $this->price->item_offer_id]]);
        if ($block) {
            $block->delete();
        }
        $this->cart->save();
    }

    public function discount()
    {
        if ($this->cart->card && $this->price->offer->item->type && $this->price->offer->item->type->discount) {
            return round($this->price->price * $this->cart->card->level->discount->amount, 2);
        }
        return 0;
    }

    public static $labels = [
        'item_id' => 'Товар',
        'item_offer_price_id' => 'Цена в каталоге',
        'count' => 'Количество',
        'cart_id' => 'Корзина',
        'final_price' => 'Итоговая цена за единицу',
    ];
    public static $cols = [
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'cart_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'cart'],
        'item_offer_price_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'price', 'showCol' => 'price'],
        'count' => ['type' => 'text'],
        'final_price' => ['type' => 'decimal'],
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Покупки',
            'cols' => [
                'item_id',
                'item_offer_price_id',
                'price:currency' => ['label' => 'Валюта'],
                'count',
            ],
        ],
    ];
    public static $forms = [
        'manager' => [
            'relations' => [
                'item_id' => [
                    'col' => 'item_offer_price_id',
                    'model' => 'Item',
                    'relation' => 'prices'
                ]
            ],
            'map' => [
                ['cart_id', 'item_id', 'item_offer_price_id'],
                ['final_price', 'count'],
            ]
        ]
    ];

    public static function relations()
    {
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
