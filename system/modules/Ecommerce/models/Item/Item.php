<?php

/**
 * Ecommerce item model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Item extends \Model {

    static $categoryModel = 'Ecommerce\Category';
    static $objectName = 'Товар';
    static $labels = [
        'name' => 'Название',
        'category_id' => 'Раздел',
        'description' => 'Описание',
        'item_type_id' => 'Тип товара',
        'image_file_id' => 'Изображение',
        'best' => 'Лучшее предложение',
        'options' => 'Параметры',
        'offers' => 'Торговые предложения',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'category_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'category'],
        'description' => ['type' => 'html'],
        'item_type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'image_file_id' => ['type' => 'image'],
        'best' => ['type' => 'bool'],
        'options' => ['type' => 'dataManager', 'relation' => 'options'],
        'offers' => ['type' => 'dataManager', 'relation' => 'offers'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Товары',
            'cols' => [
                'name',
                'category_id',
                'item_type_id',
                'best',
                'options',
                'offers',
            ],
            'categorys' => [
                'model' => 'Ecommerce\Category',
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'category_id'],
                ['item_type_id', 'best', 'image_file_id'],
                ['description'],
                ['options'],
                ['offers'],
            ]
    ]];

    static function itemsListOld() {
        /**
          SELECT *,COALESCE(sum(ciw_count),0) AS warehouse,(

          SELECT COALESCE(sum(ewb_count) ,0) as `sum`
          FROM `rodnik`.`inji_ecommerce_warehouses_block`
          inner JOIN `inji_category_carts`
          ON cc_id = ewb_cc_id AND ( (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE)) )
          WHERE `ewb_id` = ici.id

          ) as `blocked`
          FROM inji_category_items ici
          LEFT JOIN `inji_category_item_warehouses` ON `ciw_id` = ici.id
          GROUP BY ici.id
         */
        \App::$cur->db->cols = '*,COALESCE(sum(ciw_count),0) AS warehouse,(

SELECT COALESCE(sum(ewb_count) ,0) as `sum` 
  FROM `inji_ecommerce_warehouses_block` 
  inner JOIN `inji_category_carts` 
  ON cc_id = ewb_cc_id AND ( (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE)) ) 
  WHERE `ewb_id` = id
  
  ) as `blocked`, cip_value';
        \App::$cur->db->join('category_item_warehouses', '`ciw_id` = id');
        \App::$cur->db->join('category_items_params', '`cip_id` = id and cip_cio_id = 1');
        \App::$cur->db->group('id');
        \App::$cur->db->order('name');
        $items = Item::get_list();
        foreach ($items as $key => $item) {
            $item->combined = ($item->cip_value ? $item->cip_value : $item->name) . ' (' . ($item->warehouse - $item->blocked) . ' на складе)';
        }
        return $items;
    }

    function itemNameCount() {
        \App::$cur->db->where('cip_id', $this->id);
        \App::$cur->db->where('cip_cio_id', 1);
        $param = \App::$cur->db->select('category_items_params')->fetch_assoc();
        if (!empty($param['cip_value'])) {
            $itemName = $param['cip_value'];
        } else {
            $itemName = $this->name;
        }
        return $itemName . ' (' . $this->warehouseCount() . ' на складе)';
    }

    function beforeSave() {
        if ($this->id) {
            $this->search_index = $this->name . ' ';
            if ($this->category) {
                $this->search_index .= $this->category->category_name . ' ';
            }
            if ($this->options)
                foreach ($this->options as $option) {
                    if ($option->cio_searchable && $option->cip_value) {
                        if ($option->cio_type != 'select') {
                            $this->search_index .= $option->cip_value . ' ';
                        } else {
                            $data = json_decode($option->cio_advance, true);
                            if (!empty($data['data'][$option->cip_value])) {
                                $this->search_index .= $data['data'][$option->cip_value] . ' ';
                            }
                        }
                    }
                }
        }
    }

    function warehouseCount($cc_id = 0) {
        $ids = array_keys($this->offers);
        \App::$cur->db->where(Item\Offer\Warehouse::colPrefix() . Item\Offer::index(), implode(',', $ids), 'IN');
        \App::$cur->db->cols = 'COALESCE(sum(' . Item\Offer\Warehouse::colPrefix() . 'count),0) as `sum` ';
        $warehouse = \App::$cur->db->select(Item\Offer\Warehouse::table())->fetch();

        \App::$cur->db->cols = 'COALESCE(sum(' . Warehouse\Block::colPrefix() . 'count) ,0) as `sum` ';
        \App::$cur->db->where(Warehouse\Block::colPrefix() . Item\Offer::index(), implode(',', $ids), 'IN');
        if ($cc_id) {
            \App::$cur->db->where(Warehouse\Block::colPrefix() . Cart::index(), (int) $cc_id, '!=');
        }
        $on = '
            ' . Cart::index() . ' = ' . Warehouse\Block::colPrefix() . Cart::index() . ' AND (
            (`'.Cart::colPrefix().'warehouse_block` = 1 and `'.Cart::colPrefix().'cart_status_id` in(2,3,6)) || 
            (`'.Cart::colPrefix().'cart_status_id` in(0,1) and `'.Cart::colPrefix().'date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
            )
        ';
        \App::$cur->db->join(Cart::table(), $on, 'inner');

        $blocked = \App::$cur->db->select(Warehouse\Block::table())->fetch();
        return (float) $warehouse['sum'] - (float) $blocked['sum'];
    }

    function changeWarehouse($count) {
        $warehouse = ItemWarehouses::get([['ciw_count', '0', '>'], ['ciw_id', $this->id]]);
        if ($warehouse) {
            $warehouse->ciw_count +=(float) $count;
            $warehouse->save();
        }
    }

    static function relations() {

        return [
            'category' => [
                'model' => 'Ecommerce\Category',
                'col' => 'category_id'
            ],
            'options' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Param',
                'col' => 'item_id',
                //'resultKey' => 'code',
                'params' => [
                    'join' => [Item\Option::table(), Item\Option::index() . ' = ' . Item\Param::colPrefix() . Item\Option::index()]
                ]
            ],
            'offers' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Offer',
                'col' => 'item_id',
            ],
            'type' => [
                'model' => 'Ecommerce\Item\Type',
                'col' => 'item_type_id',
            ],
        ];
    }

    function price() {
        $prices = $this->prices;
        return $prices[key($prices)];
    }

}
