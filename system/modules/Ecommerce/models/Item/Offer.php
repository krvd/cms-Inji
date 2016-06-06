<?php

/**
 * Item offer
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Offer extends \Model
{
    public static $objectName = 'Торговое предложение';
    public static $cols = [
        //Основные параметры
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'name' => ['type' => 'text'],
        'article' => ['type' => 'text'],
        //Системные
        'weight' => ['type' => 'number'],
        'date_create' => ['type' => 'dateTime'],
        //Менеджеры
        'warehouse' => ['type' => 'dataManager', 'relation' => 'warehouses'],
        'price' => ['type' => 'dataManager', 'relation' => 'prices'],
        'option' => ['type' => 'dataManager', 'relation' => 'options'],
    ];
    public static $labels = [
        'name' => 'Название',
        'article' => 'Артикул',
        'warehouse' => 'Наличие на складах',
        'price' => 'Цены',
        'option' => 'Параметры предложения'
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'article', 'warehouse', 'price', 'option'
            ]
        ]
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name', 'article'],
                ['warehouse'],
                ['price'],
                ['option'],
            ]
        ]
    ];

    public static function relations()
    {
        return [
            'warehouses' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Warehouse',
                'col' => 'item_offer_id'
            ],
            'prices' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Price',
                'col' => 'item_offer_id',
            ],
            'bonuses' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Bonus',
                'col' => 'item_offer_id',
            ],
            'options' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer\Param',
                'col' => 'item_offer_id',
                //'resultKey' => 'code',
                'resultKey' => 'item_offer_option_id',
                'join' => [Offer\Option::table(), Offer\Option::index() . ' = ' . Offer\Param::colPrefix() . Offer\Option::index()]
            ],
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ]
        ];
    }

    public function changeWarehouse($count)
    {
        $warehouse = Offer\Warehouse::get([['count', '0', '>'], ['item_offer_id', $this->id]]);
        if ($warehouse) {
            $warehouse->count +=(float) $count;
            $warehouse->save();
        } else {
            $warehouse = Offer\Warehouse::get([['item_offer_id', $this->id]]);
            if ($warehouse) {
                $warehouse->count +=(float) $count;
                $warehouse->save();
            }
        }
    }

    public function warehouseCount($cart_id = 0)
    {
        $warehouseIds = [];
        if (class_exists('Geography\City\Data')) {
            $warehouses = \Geography\City\Data::get([['code', 'warehouses'], ['city_id', \Geography\City::$cur->id]]);
            if ($warehouses && $warehouses->data) {
                foreach (explode(',', $warehouses->data) as $id) {
                    $warehouseIds[$id] = $id;
                }
            }
        }
        if ($warehouseIds) {
            \App::$cur->db->where(\Ecommerce\Item\Offer\Warehouse::colPrefix() . \Ecommerce\Warehouse::index(), $warehouseIds, 'IN');
        }
        \App::$cur->db->where(\Ecommerce\Item\Offer\Warehouse::colPrefix() . \Ecommerce\Item\Offer::index(), $this->id);
        \App::$cur->db->cols = 'COALESCE(sum(' . \Ecommerce\Item\Offer\Warehouse::colPrefix() . 'count),0) as `sum` ';
        $warehouse = \App::$cur->db->select(\Ecommerce\Item\Offer\Warehouse::table())->fetch();

        \App::$cur->db->cols = 'COALESCE(sum(' . \Ecommerce\Warehouse\Block::colPrefix() . 'count) ,0) as `sum` ';
        \App::$cur->db->where(\Ecommerce\Warehouse\Block::colPrefix() . \Ecommerce\Item\Offer::index(), $this->id);
        if ($cart_id) {
            \App::$cur->db->where(\Ecommerce\Warehouse\Block::colPrefix() . \Ecommerce\Cart::index(), (int) $cart_id, '!=');
        }
        $on = '
            ' . \Ecommerce\Cart::index() . ' = ' . \Ecommerce\Warehouse\Block::colPrefix() . \Ecommerce\Cart::index() . ' AND (
            (`' . \Ecommerce\Cart::colPrefix() . 'warehouse_block` = 1 and `' . \Ecommerce\Cart::colPrefix() . 'cart_status_id` in(2,3,6)) || 
            (`' . \Ecommerce\Cart::colPrefix() . 'cart_status_id` in(0,1) and `' . \Ecommerce\Cart::colPrefix() . 'date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
            )
        ';
        \App::$cur->db->join(\Ecommerce\Cart::table(), $on, 'inner');

        $blocked = \App::$cur->db->select(\Ecommerce\Warehouse\Block::table())->fetch();
        return (float) $warehouse['sum'] - (float) $blocked['sum'];
    }

    public function beforeDelete()
    {
        if ($this->id) {
            if ($this->warehouses) {
                foreach ($this->warehouses as $warehouse) {
                    $warehouse->delete();
                }
            }
            if ($this->prices) {
                foreach ($this->prices as $price) {
                    $price->delete();
                }
            }
            if ($this->bonuses) {
                foreach ($this->bonuses as $bonus) {
                    $bonus->delete();
                }
            }
        }
    }

}
