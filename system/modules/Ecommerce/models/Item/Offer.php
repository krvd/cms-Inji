<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Offer extends \Model
{
    static $objectName = 'Торговое предложение';
    static $cols = [
        'name' => ['type' => 'text'],
        'article' => ['type' => 'text'],
        'warehouse' => ['type' => 'dataManager', 'relation' => 'warehouses'],
        'price' => ['type' => 'dataManager', 'relation' => 'prices'],
    ];
    static $labels = [
        'name' => 'Название',
        'article' => 'Артикул',
        'warehouse' => 'Наличие на складах',
        'price' => 'Цены',
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name', 'article', 'warehouse', 'price'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'article'],
                ['warehouse'],
                ['price']
            ]
        ]
    ];

    static function relations()
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
            'item' => [
                'model' => 'Ecommerce\Item',
                'col' => 'item_id'
            ]
        ];
    }

    function changeWarehouse($count)
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

    function warehouseCount($cart_id = 0)
    {
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

    function beforeDelete()
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
        }
    }

}
