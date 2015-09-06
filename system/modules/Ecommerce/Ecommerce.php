<?php

class Ecommerce extends Module {

    function init() {
        App::$primary->view->customAsset('js', '/moduleAsset/Ecommerce/js/cart.js');
    }

    function getCurCart() {
        $cart = false;
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Ecommerce\Cart::get((int) $_SESSION['cart']['cart_id']);
        }
        if (!$cart) {
            $cart = new Ecommerce\Cart();
            $cart->cart_status_id = 1;
            $cart->user_id = Users\User::$cur->id;
            $cart->save();
            $_SESSION['cart']['cart_id'] = $cart->id;
        }
        return $cart;
    }

    function getBreadcrumb() {
        $bread = [];
        $bread['/ecommerce'] = 'Онлайн-магазин';
        if (!empty($this->view->content_data['catalog'])) {

            $ids = explode('/', $this->view->content_data['catalog']->catalog_tree_path);
            foreach ($ids as $id) {
                if ($catalog = Catalog::get((int) $id)) {
                    $bread['/ecommerce/itemList/' . $id] = $catalog->catalog_name;
                }
            }
            $bread['/ecommerce/itemList/' . $this->view->content_data['catalog']->catalog_id] = $this->view->content_data['catalog']->catalog_name;
        }
        return $bread;
    }

    function parseOptions($options = []) {
        $selectOptions = [
            'where' => !empty($options['where']) ? $options['where'] : [],
            'distinct' => false,
            'join' => [],
            'order' => [],
            'start' => isset($options['start']) ? (int) $options['start'] : 0,
            'limit' => !empty($options['count']) ? (int) $options['count'] : 0,
        ];
        if (!empty($options['sort']) && is_array($options['sort'])) {
            foreach ($options['sort'] as $col => $direction) {
                switch ($col) {
                    case 'price':
                        $selectOptions['order'][] = [Ecommerce\Item\Offer\Price::colPrefix() . 'price', strtolower($direction) == 'desc' ? 'desc' : 'asc'];
                        break;
                }
            }
        } elseif (!empty($options['sort'])) {
            //echo $options['sort'];
        }


        //filters
        if (!empty($options['filters'])) {
            foreach ($options['filters'] as $col => $filter) {
                switch ($col) {
                    case 'price':
                        if (!empty($filter['min'])) {
                            $selectOptions['where'][] = [Ecommerce\Item\Offer\Price::colPrefix() . 'price', (float) $filter['min'], '>='];
                        }
                        if (!empty($filter['max'])) {
                            $selectOptions['where'][] = [Ecommerce\Item\Offer\Price::colPrefix() . 'price', (float) $filter['max'], '<='];
                        }
                        break;
                    case 'options':
                        foreach ($filter as $optionId => $value) {
                            $selectOptions['join'][] = [Ecommerce\Item\Param::table(), Ecommerce\Item::index() . ' = ' . Ecommerce\Item\Param::colPrefix() . Ecommerce\Item::index() . ' AND ' .
                                Ecommerce\Item\Param::colPrefix() . 'value = "' . (int) $value . '"', 'inner'];
                        }
                        break;
                }
            }
        }
        //parents
        if (!empty($options['parent']) && strpos($options['parent'], ',') !== false) {
            $first = true;
            $where = [];
            foreach (explode(',', $options['parent']) as $categoryId) {
                if (!$categoryId) {
                    continue;
                }
                $category = \Ecommerce\Category::get($categoryId);
                $where[] = ['tree_path', $category->tree_path . (int) $categoryId . '/%', 'LIKE', $first ? 'AND' : 'OR'];
                $first = false;
            }
            $selectOptions['where'][] = $where;
        } elseif (!empty($options['parent'])) {
            $category = \Ecommerce\Category::get($options['parent']);
            $selectOptions['where'][] = ['tree_path', $category->tree_path . (int) $options['parent'] . '/%', 'LIKE'];
        }

        //search
        if (!empty($options['search'])) {
            $selectOptions['where'][] = ['search_index', '%' . $options['search'] . '%', 'LIKE'];
        }

        if (empty($this->config['view_empty_warehouse'])) {
            $selectOptions['where'][] = [
                '(
          (SELECT COALESCE(sum(`' . \Ecommerce\Item\Offer\Warehouse::colPrefix() . 'count`),0) 
            FROM ' . \App::$cur->db->table_prefix . \Ecommerce\Item\Offer\Warehouse::table() . ' iciw 
            WHERE iciw.' . \Ecommerce\Item\Offer\Warehouse::colPrefix() . \Ecommerce\Item\Offer::index() . ' = ' . \Ecommerce\Item\Offer::index() . '
            )
          -
          (SELECT COALESCE(sum(' . \Ecommerce\Warehouse\Block::colPrefix() . 'count) ,0)
            FROM ' . \App::$cur->db->table_prefix . \Ecommerce\Warehouse\Block::table() . ' iewb
            inner JOIN ' . \App::$cur->db->table_prefix . \Ecommerce\Cart::table() . ' icc ON icc.' . \Ecommerce\Cart::index() . ' = iewb.' . \Ecommerce\Warehouse\Block::colPrefix() . \Ecommerce\Cart::index() . ' AND (
                (`' . \Ecommerce\Cart::colPrefix() . 'warehouse_block` = 1 and `' . \Ecommerce\Cart::colPrefix() . 'cart_status_id` in(2,3,6)) ||
                (`' . \Ecommerce\Cart::colPrefix() . \Ecommerce\Cart\Status::index() . '` in(0,1) and `' . \Ecommerce\Cart::colPrefix() . 'date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
            )
            WHERE iewb.' . \Ecommerce\Warehouse\Block::colPrefix() . \Ecommerce\Item\Offer::index() . ' = ' . \Ecommerce\Item\Offer::index() . ')
          )',
                0,
                '>'
            ];
        }


        $selectOptions['join'][] = [Ecommerce\Item\Offer::table(), Ecommerce\Item::index() . ' = ' . Ecommerce\Item\Offer::colPrefix() . Ecommerce\Item::index(), 'inner'];
        $selectOptions['join'][] = [Ecommerce\Item\Offer\Price::table(),
            Ecommerce\Item\Offer::index() . ' = ' . Ecommerce\Item\Offer\Price::colPrefix() . Ecommerce\Item\Offer::index() . ' and ' . Ecommerce\Item\Offer\Price::colPrefix() . 'price>0', 'inner'];
        $selectOptions['join'][] = [Ecommerce\Item\Offer\Price\Type::table(), Ecommerce\Item\Offer\Price::colPrefix() . Ecommerce\Item\Offer\Price\Type::index() . ' = ' . Ecommerce\Item\Offer\Price\Type::index() .
            ' and (' . Ecommerce\Item\Offer\Price\Type::colPrefix() . 'roles="" || ' . Ecommerce\Item\Offer\Price\Type::colPrefix() . 'roles LIKE "%|' . \Users\User::$cur->role_id . '|%")'
            , 'inner'];

        $selectOptions['group'] = Ecommerce\Item::index();

        return $selectOptions;
    }

    /**
     * Getting items with params
     * 
     * @param array $params
     * @return array
     */
    function getItems($options = []) {
        $selectOptions = $this->parseOptions($options);

        //\App::$cur->db->cols = 'DISTINCT `' . \App::$cur->db->table_prefix . 'catalog_items`. *';

        /* if (empty($this->modConf['view_empty_warehouse'])) {
          \App::$cur->db->join('catalog_items_options', '`cio_code` = "itemImage"', 'inner');
          \App::$cur->db->join('catalog_items_params', '`ci_id` = `cip_ci_id` and cip_cio_id = cio_id and `cip_value`!=""', 'inner');

          if (\App::$cur->db->where) {
          \App::$cur->db->where .= ' AND ';
          } else {
          \App::$cur->db->where = 'WHERE ';
          }
          \App::$cur->db->where .= '
          ((SELECT COALESCE(sum(`ciw_count`),0) FROM inji_catalog_item_warehouses iciw WHERE iciw.ciw_ci_id = ci_id)-
          (SELECT COALESCE(sum(ewb_count) ,0)
          FROM inji_ecommerce_warehouses_block iewb
          inner JOIN inji_catalog_carts icc ON icc.cc_id = iewb.ewb_cc_id AND (
          (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) ||
          (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
          )
          WHERE iewb.ewb_ci_id = ci_id))>0 ';

          //\App::$cur->db->join('catalog_carts', '`cc_warehouse_block` = 1 and `cc_status` in(2,3)');
          //\App::$cur->db->join('catalog_cart_items', '`cci_cc_id` = `cc_id` and `cci_ci_id`= `ci_id`');
          }

          //best,asc,desc,hit,priceasc,pricedesc
          switch ($sort) {
          case 'best':
          \App::$cur->db->where('ci_best', 1);
          \App::$cur->db->order = 'ORDER BY RAND()';
          break;
          case 'asc':
          \App::$cur->db->order('ci_name', 'ASC');
          break;
          case 'desc':
          \App::$cur->db->order('ci_name', 'DESC');
          break;
          case 'rand':
          \App::$cur->db->order = 'ORDER BY RAND()';
          break;
          case 'hit':
          \App::$cur->db->order('ci_sales', 'DESC');
          break;
          case 'promo':
          \App::$cur->db->cols = '*';
          \App::$cur->db->order = 'ORDER BY RAND()';
          if (\App::$cur->db->where)
          \App::$cur->db->where .= ' AND (select `cip_value` from `' . \App::$cur->db->table_prefix . 'catalog_items_params` where `cip_ci_id` = `ci_id` and `cip_cio_id` = 50 limit 1) = 1 ';
          else
          \App::$cur->db->where = 'WHERE (select `cip_value` from `' . \App::$cur->db->table_prefix . 'catalog_items_params` where `cip_ci_id` = `ci_id` and `cip_cio_id` = 50 limit 1) = 1 ';
          break;
          case 'priceasc':
          \App::$cur->db->order('price', 'ASC');
          \App::$cur->db->cols = '*,(select `ciprice_price` from `' . \App::$cur->db->table_prefix . 'catalog_items_prices` where `ciprice_ci_id` = `ci_id` order by `ciprice_price` asc limit 1) as `price`';
          break;
          case 'pricedesc':
          \App::$cur->db->order('price', 'DESC');
          \App::$cur->db->cols = '*,(select `ciprice_price` from `' . \App::$cur->db->table_prefix . 'catalog_items_prices` where `ciprice_ci_id` = `ci_id` order by `ciprice_price` asc limit 1) as `price`';
          break;
          } */
        $items = Ecommerce\Item::getList($selectOptions);
        return $items;
    }

    /**
     * Return count of items with params
     * 
     * @param array $params
     * @return int
     */
    function getItemsCount($options = []) {
        $selectOptions = $this->parseOptions($options);
        $selectOptions['distinct'] = \Ecommerce\Item::index();
        $counts = Ecommerce\Item::getCount($selectOptions);
        if (is_array($counts)) {
            $sum = 0;
            foreach ($counts as $count) {
                $sum +=$count['count'];
            }
            return $sum;
        }
        return $counts;
    }

}

?>
