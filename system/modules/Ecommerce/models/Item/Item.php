<?php

namespace Ecommerce;

class Item extends \Model {

    static $categoryModel = 'Ecommerce\Category';
    static $labels = [
        'name' => 'Название',
        'category_id' => 'Раздел',
        'description' => 'Описание',
        'item_type_id' => 'Тип товара',
        'image_file_id' => 'Изображение',
        'best' => 'Лучшее предложение',
        'options' => 'Параметры',
        'prices' => 'Цены',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'category_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'category'],
        'options' => ['type' => 'dataManager', 'relation' => 'options'],
        'prices' => ['type' => 'dataManager', 'relation' => 'prices'],
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
                'prices',
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
                ['options'],
                ['prices'],
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
        Inji::app()->db->cols = '*,COALESCE(sum(ciw_count),0) AS warehouse,(

SELECT COALESCE(sum(ewb_count) ,0) as `sum` 
  FROM `inji_ecommerce_warehouses_block` 
  inner JOIN `inji_category_carts` 
  ON cc_id = ewb_cc_id AND ( (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE)) ) 
  WHERE `ewb_id` = id
  
  ) as `blocked`, cip_value';
        Inji::app()->db->join('category_item_warehouses', '`ciw_id` = id');
        Inji::app()->db->join('category_items_params', '`cip_id` = id and cip_cio_id = 1');
        Inji::app()->db->group('id');
        Inji::app()->db->order('name');
        $items = Item::get_list();
        foreach ($items as $key => $item) {
            $item->combined = ($item->cip_value ? $item->cip_value : $item->name) . ' (' . ($item->warehouse - $item->blocked) . ' на складе)';
        }
        return $items;
    }

    function itemNameCount() {
        Inji::app()->db->where('cip_id', $this->id);
        Inji::app()->db->where('cip_cio_id', 1);
        $param = Inji::app()->db->select('category_items_params')->fetch_assoc();
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
        Inji::app()->db->where('ciw_id', $this->id);
        Inji::app()->db->cols = 'COALESCE(sum(ciw_count),0) as `sum` ';
        $warehouse = Inji::app()->db->select('category_item_warehouses')->fetch_assoc();
        Inji::app()->db->cols = 'COALESCE(sum(ewb_count) ,0) as `sum` ';
        Inji::app()->db->where('ewb_id', $this->id);
        if ($cc_id) {
            Inji::app()->db->where('cc_id', (int) $cc_id, '!=');
        }
        $on = '
            cc_id = ewb_cc_id AND (
            (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || 
            (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
            )
        ';
        Inji::app()->db->join('category_carts', $on, 'inner');

        $blocked = Inji::app()->db->select('ecommerce_warehouses_block')->fetch_assoc();
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
            'prices' => [
                'type' => 'many',
                'model' => 'Ecommerce\Item\Price',
                'col' => 'item_id',
            ],
            'unit' => [
                'model' => 'ItemUnit',
                'col' => 'unit',
            ],
            'type' => [
                'model' => 'ItemType',
                'col' => 'cit_id',
            ],
        ];
    }

    function price() {
        $prices = $this->prices;
        return $prices[key($prices)];
    }

}
