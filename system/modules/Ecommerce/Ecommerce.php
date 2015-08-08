<?php

class Ecommerce extends Module {

    function init() {
        App::$primary->view->customAsset('js', '/moduleAsset/Ecommerce/js/cart.js');
    }

    function getCurCart() {
        $cart = false;
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Cart::get((int) $_SESSION['cart']['cart_id']);
        }
        if (!$cart) {
            $cart = new Cart();
            $cart->cc_status = 1;
            $cart->cc_user_id = $this->users->cur->user_id;
            $cart->save();
            $_SESSION['cart']['cart_id'] = $cart->cc_id;
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

    function dayItemCountReport() {
        $catalogIds = [];
        $table = '<table border = "2px">';
        $catalogs = Catalog::get_list(['order' => ['catalog_name', 'asc']]);
        foreach ($catalogs as $catalog) {
            if ($catalog->catalog_parent_id == 0) {
                $table .=$this->showChildsCatalogs($catalog, $catalogs, $catalogIds, -1);
            }
        }
        $table .= '<table>';
        $this->_MAIL->send('noreply@' . INJI_DOMAIN_NAME, 'admin@inji.ru', 'Отчет о статистике товаров с сайта ' . INJI_DOMAIN_NAME, $table);
    }

    function showChildsCatalogs($parent, $catalogs, $catalogIds, $i) {
        $i++;
        $isset = false;
        $table = '';
        foreach ($catalogs as $catalog) {
            if ($catalog->catalog_parent_id == $parent->catalog_id) {
                if (!$isset) {
                    $isset = true;
                    $itemsCount = Inji::app()->ecommerce->getItemsCount($parent->catalog_id, '');
                    $table .= "<tr><td>" . str_repeat('• ', $i) . "<a href='http://" . INJI_DOMAIN_NAME . "/ecommerce/itemList/{$parent->catalog_id}'>{$parent->catalog_name}</a></td><td>{$itemsCount}</td></tr>";
                }
                $table .= $this->showChildsCatalogs($catalog, $catalogs, $catalogIds, $i);
            }
        }
        if (!$isset) {
            $itemsCount = Inji::app()->ecommerce->getItemsCount($parent->catalog_id, '');
            $table .= "<tr><td>" . str_repeat('• ', $i) . "<a href='http://" . INJI_DOMAIN_NAME . "/ecommerce/itemList/{$parent->catalog_id}'>{$parent->catalog_name}</a></td><td>{$itemsCount}</td></tr>";
        }
        return $table;
    }

    function goMarketing($cart) {
        $catalogs = [];

        \App::$cur->db->order('lm_level');
        $levelMarketing = \App::$cur->db->result_array(\App::$cur->db->select('level_marketing'));
        $levels = array();
        foreach ($levelMarketing as $row) {
            $levels[$row['lm_level']][] = $row;
            if (is_numeric($row['lm_item_type']) && !in_array($row['lm_item_type'], $catalogs)) {
                $catalogs[] = $row['lm_item_type'];
            }
        }

        $sums = [];
        foreach ($cart->cartItems as $cci) {
            if ($cci->cci_ci_id == Inji::app()->ecommerce->modConf['packItem']['ci_id'] || $cci->cci_ci_id == Inji::app()->ecommerce->modConf['cardItem']['ci_id']) {
                continue;
            }
            if ($cci->item->ci_item_price_type == 'Обычная' || $cci->item->ci_item_price_type = 'Социальная группа товаров (Бонусы)') {
                $sums[] = [
                    'sum' => $cci->cci_final_price ? $cci->cci_final_price : $cci->price->ciprice_price,
                    'count' => $cci->cci_count,
                    'catalogTree' => $cci->item->ci_tree_path
                ];
            }
        }

        $user = $cart->user;
        $last_level = 0;
        foreach ($levels as $levelNum => $level) {
            if ($levelNum != $last_level) {
                if ($user->parent)
                    $user = $user->parent;
                else {
                    break;
                }
            }
            foreach ($level as $action) {
                $bonus = 0;
                switch ($action['lm_type']) {
                    case 'Процент':
                        if (is_numeric($action['lm_item_type'])) {
                            foreach ($sums as $sum) {
                                if (strpos($sum['catalogTree'], "/{$action['lm_item_type']}/") !== FALSE) {
                                    $bonus += round($sum['sum'] / 100 * $action['lm_sum'] * $sum['count'], 2);
                                }
                            }
                            if ($bonus)
                                \App::$cur->db->insert('catalog_user_bonuses', [
                                    'cub_user_id' => $user->user_id,
                                    'cub_sum' => $bonus,
                                    'cub_level' => $levelNum,
                                    'cub_type' => $action['lm_type'],
                                    'cub_cart_id' => $cart->cc_id,
                                    'cub_curency' => $action['lm_curency'],
                                    'cub_marketing_type' => $action['lm_item_type'],
                                    'cub_true' => ( ($action['lm_role_id'] == $user->role->role_id) ? 1 : 0),
                                    'cub_date' => ($cart->cc_payed_date != '0000-00-00 00:00:00') ? $cart->cc_payed_date : $cart->cc_date,
                                ]);
                        }
                        break;
                    case 'Сумма':
                        if ($action['lm_item_type'] == 'Клубная карта') {
                            if ($cart->cc_card_buy) {
                                \App::$cur->db->insert('catalog_user_bonuses', [
                                    'cub_user_id' => $user->user_id,
                                    'cub_sum' => $action['lm_sum'],
                                    'cub_level' => $levelNum,
                                    'cub_type' => $action['lm_type'],
                                    'cub_cart_id' => $cart->cc_id,
                                    'cub_curency' => $action['lm_curency'],
                                    'cub_marketing_type' => $action['lm_item_type'],
                                    'cub_true' => ( ($action['lm_role_id'] == $user->role->role_id) ? 1 : 0),
                                    'cub_date' => ($cart->cc_payed_date != '0000-00-00 00:00:00') ? $cart->cc_payed_date : $cart->cc_date,
                                ]);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Recalculate catalogs tree path
     * 
     * @param int $catalogId
     * @return boolean
     */
    function recalcCatalogTree() {
        Catalog::update(['catalog_tree_path' => '']);
        $catalogs = Catalog::get_list();
        foreach ($catalogs as $catalog) {
            $catalog->save();
        }
        return true;
    }

    /**
     * Get catalog tree path
     * 
     * @param object $catalog
     * @return string
     */
    function getCatalogTree($catalog) {
        if ($catalog->catalog_parent_id) {
            $parent = Catalog::get($catalog->catalog_parent_id);
            if ($parent && $parent->catalog_tree_path) {
                return $parent->catalog_tree_path . $parent->catalog_id . '/';
            } else {
                return $this->getCatalogTree($parent) . $parent->catalog_id . '/';
            }
        }
        return '/';
    }

    /**
     * Getting items with many params
     * 
     * @param mixed $parent
     * @param int $start
     * @param int $count
     * @param string $key
     * @param string $search
     * @param string $sort
     * @return array
     */
    function getItems($parent = '', $start = 0, $count = 0, $key = 'ci_id', $search = '', $sort = 'asc') {
        if (is_array($parent)) {
            extract($parent);
        }
        if (is_array($parent)) {
            $parent = '';
        }
        $selectOptions = [
            'where' => [],
            'distinct' => false,
            'join' => [],
            'start' => $start,
            'limit' => $count ? $count : false,
        ];
        if (strpos($parent, ',') !== false) {

            $ids = explode(',', $parent);
            $first = true;
            foreach ($ids as $id) {
                $category = Ecommerce\Category::get((int) $id);
                if ($category) {
                    $selectOptions['where'][] = ['tree_path', $category->tree_path . $category->id . '/%', 'LIKE', $first ? 'AND' : 'OR'];
                    $first = false;
                }
            }
        } elseif ($parent !== '') {
            $category = Ecommerce\Category::get((int) $parent);
            if ($category) {
                $selectOptions['where'][] = ['tree_path', $category->tree_path . $category->id . '/%', 'LIKE'];
            }
        }
        if (!empty($search)) {
            $search = str_replace(' ', '%', $search);
            $ids = Ecommerce\Item::getList(['where' => ['search_index', '%' . $search . '%', 'LIKE'], 'array' => true]);
            $ids = array_keys($ids);
            if (!$ids)
                return [];
            $selectOptions['where'][] = ['id', implode(',', $ids), 'IN'];
        }
        $selectOptions['join'][] = [Ecommerce\Item\Offer::table(), Ecommerce\Item::index() . ' = ' . Ecommerce\Item\Offer::colPrefix() . Ecommerce\Item::index(), 'inner'];
        $selectOptions['join'][] = [Ecommerce\Item\Offer\Price::table(), Ecommerce\Item\Offer::index() . ' = ' . Ecommerce\Item\Offer\Price::colPrefix() . Ecommerce\Item\Offer::index() . ' and ' . Ecommerce\Item\Offer\Price::colPrefix() . 'price>0', 'inner'];

        $selectOptions['distinct'] = true;
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
     * @param int $parent
     * @param string $search
     * @return int
     */
    function getItemsCount($parent = '', $search = '') {
        if (is_array($parent)) {
            extract($parent);
        }
        if (is_array($parent)) {
            $parent = '';
        }
        $selectOptions = [
            'where' => [],
            'distinct' => false,
            'join' => [],
        ];
        if (strpos($parent, ',') !== false) {

            $ids = explode(',', $parent);
            $first = true;
            foreach ($ids as $id) {
                $category = Ecommerce\Category::get((int) $id);
                if ($category) {
                    $selectOptions['where'][] = ['tree_path', $category->tree_path . $category->id . '/%', 'LIKE', $first ? 'AND' : 'OR'];
                    $first = false;
                }
            }
        } elseif ($parent !== '') {
            $category = Ecommerce\Category::get((int) $parent);
            if ($category) {
                $selectOptions['where'][] = ['tree_path', $category->tree_path . $category->id . '/%', 'LIKE'];
            }
        }
        if (!empty($search)) {
            $search = str_replace(' ', '%', $search);
            $ids = Ecommerce\Item::getList(['where' => ['search_index', '%' . $search . '%', 'LIKE'], 'array' => true]);
            $ids = array_keys($ids);
            if (!$ids)
                return [];
            $selectOptions['where'][] = ['id', implode(',', $ids), 'IN'];
        }
        $selectOptions['join'][] = [Ecommerce\Item\Offer::table(), Ecommerce\Item::index() . ' = ' . Ecommerce\Item\Offer::colPrefix() . Ecommerce\Item::index(), 'inner'];
        $selectOptions['join'][] = [Ecommerce\Item\Offer\Price::table(), Ecommerce\Item\Offer::index() . ' = ' . Ecommerce\Item\Offer\Price::colPrefix() . Ecommerce\Item\Offer::index() . ' and ' . Ecommerce\Item\Offer\Price::colPrefix() . 'price>0', 'inner'];

        $selectOptions['distinct'] = true;
        $items = Ecommerce\Item::getCount($selectOptions);
        //var_dump(App::$cur->db->lastQuery,$items);
        return $items;
    }

    function cart_recount($cc_id) {
        \App::$cur->db->where('cc_id', $cc_id);
        $cart = \App::$cur->db->select('catalog_carts')->fetch_assoc();
        if (!$cart)
            return false;
        $items = $this->get_cart_items($cc_id);
        if (!$items)
            return false;
        $pricesumm = 0;
        foreach ($items as $cartitem) {
            $item = Inji::app()->ecommerce->get_item($cartitem['cci_ci_id']);
            $price = Inji::app()->ecommerce->get_prices($item['ci_id']);
            $pricesumm += $price[$cartitem['cci_ciprice_id']]['ciprice_price'] * $cartitem['cci_count'];
        }
        $this->update_cart($cc_id, array('cc_summ' => $pricesumm));
    }

    function getCatalogParents($catalog_id, $ids = []) {
        $catalog = Ecommerce\Category::get($catalog_id);
        $ids[] = $catalog_id;
        if ($catalog->parent_id) {
            $ids = $this->getCatalogParents($catalog->parent_id, $ids);
        }
        return $ids;
    }

}

?>
