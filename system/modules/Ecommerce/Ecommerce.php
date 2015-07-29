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

        $this->db->order('lm_level');
        $levelMarketing = $this->db->result_array($this->db->select('level_marketing'));
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
                                $this->db->insert('catalog_user_bonuses', [
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
                                $this->db->insert('catalog_user_bonuses', [
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
        if (strpos($parent, ',') !== false) {
            $where = [];
            $ids = explode(',', $parent);
            $first = true;
            foreach ($ids as $id) {
                $catalog = Catalog::get((int) $id);
                if ($catalog) {
                    $where[]=['ci_tree_path', $catalog->catalog_tree_path . $catalog->catalog_id . '/%', 'LIKE', $first ? 'AND' : 'OR'];
                    $first = false;
                }
            }
            if($where){
                $this->db->where($where);
            }
        } elseif ($parent !== '') {
            $catalog = Catalog::get((int) $parent);
            if ($catalog) {
                $this->db->where('ci_tree_path', $catalog->catalog_tree_path . $catalog->catalog_id . '/%', 'LIKE');
            }
        }
        if (!empty($search)) {
            $search = str_replace(' ', '%', $search);
            $this->db->where('ci_search_index', '%' . $this->db->mysqli->real_escape_string($search) . '%', 'LIKE');
            $ids = array_keys($this->db->result_array($this->db->select('catalog_items'), 'ci_id'));
            if (!$ids)
                return array();
            $this->db->where('ci_id', implode(',', $ids), 'IN');
        }
        $this->db->join('catalog_items_prices', '`ci_id` = `ciprice_ci_id` and `ciprice_price`>0', 'inner');

        $this->db->cols = 'DISTINCT `' . $this->db->table_prefix . 'catalog_items`. *';

        if (empty($this->modConf['view_empty_warehouse'])) {
            $this->db->join('catalog_items_options', '`cio_code` = "itemImage"', 'inner');
            $this->db->join('catalog_items_params', '`ci_id` = `cip_ci_id` and cip_cio_id = cio_id and `cip_value`!=""', 'inner');

            if ($this->db->where) {
                $this->db->where .= ' AND ';
            } else {
                $this->db->where = 'WHERE ';
            }
            $this->db->where .= '  
  ((SELECT COALESCE(sum(`ciw_count`),0) FROM inji_catalog_item_warehouses iciw WHERE iciw.ciw_ci_id = ci_id)-
  (SELECT COALESCE(sum(ewb_count) ,0)
  FROM inji_ecommerce_warehouses_block iewb 
  inner JOIN inji_catalog_carts icc ON icc.cc_id = iewb.ewb_cc_id AND (
  (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || 
  (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
  )
  WHERE iewb.ewb_ci_id = ci_id))>0 ';

            //$this->db->join('catalog_carts', '`cc_warehouse_block` = 1 and `cc_status` in(2,3)');
            //$this->db->join('catalog_cart_items', '`cci_cc_id` = `cc_id` and `cci_ci_id`= `ci_id`');
        }





        if ($start || $count)
            $this->db->limit($start, $count);
//best,asc,desc,hit,priceasc,pricedesc
        switch ($sort) {
            case 'best':
                $this->db->where('ci_best', 1);
                $this->db->order = 'ORDER BY RAND()';
                break;
            case 'asc':
                $this->db->order('ci_name', 'ASC');
                break;
            case 'desc':
                $this->db->order('ci_name', 'DESC');
                break;
            case 'rand':
                $this->db->order = 'ORDER BY RAND()';
                break;
            case 'hit':
                $this->db->order('ci_sales', 'DESC');
                break;
            case 'promo':
                $this->db->cols = '*';
                $this->db->order = 'ORDER BY RAND()';
                if ($this->db->where)
                    $this->db->where .= ' AND (select `cip_value` from `' . $this->db->table_prefix . 'catalog_items_params` where `cip_ci_id` = `ci_id` and `cip_cio_id` = 50 limit 1) = 1 ';
                else
                    $this->db->where = 'WHERE (select `cip_value` from `' . $this->db->table_prefix . 'catalog_items_params` where `cip_ci_id` = `ci_id` and `cip_cio_id` = 50 limit 1) = 1 ';
                break;
            case 'priceasc':
                $this->db->order('price', 'ASC');
                $this->db->cols = '*,(select `ciprice_price` from `' . $this->db->table_prefix . 'catalog_items_prices` where `ciprice_ci_id` = `ci_id` order by `ciprice_price` asc limit 1) as `price`';
                break;
            case 'pricedesc':
                $this->db->order('price', 'DESC');
                $this->db->cols = '*,(select `ciprice_price` from `' . $this->db->table_prefix . 'catalog_items_prices` where `ciprice_ci_id` = `ci_id` order by `ciprice_price` asc limit 1) as `price`';
                break;
        }
        return Item::get_list(['key' => $key]);
    }

    /**
     * Return count of items with params
     * 
     * @param int $parent
     * @param string $search
     * @return int
     */
    function getItemsCount($parent = '', $search = '') {
        if (strpos($parent, ',') !== false) {
            $where = [];
            $ids = explode(',', $parent);
            $first = true;
            foreach ($ids as $id) {
                $catalog = Catalog::get((int) $id);
                if ($catalog) {
                    $where[]=['ci_tree_path', $catalog->catalog_tree_path . $catalog->catalog_id . '/%', 'LIKE', $first ? 'AND' : 'OR'];
                    $first = false;
                }
            }
            if($where){
                $this->db->where($where);
            }
        } elseif ($parent !== '') {
            $catalog = Catalog::get((int) $parent);
            if ($catalog) {
                $this->db->where('ci_tree_path', $catalog->catalog_tree_path . $catalog->catalog_id . '/%', 'LIKE');
            }
        }

        if (!empty($search)) {
            $search = str_replace(' ', '%', $search);
            $this->db->where('ci_search_index', '%' . $this->db->mysqli->real_escape_string($search) . '%', 'LIKE');
            $ids = array_keys($this->db->result_array($this->db->select('catalog_items'), 'ci_id'));
            if (!$ids)
                return 0;
            $this->db->where('ci_id', implode(',', $ids), 'IN');
        }
        $this->db->join('catalog_items_prices', '`ci_id` = `ciprice_ci_id` and `ciprice_price`>0', 'inner');


        if (empty($this->modConf['view_empty_warehouse'])) {
            $this->db->join('catalog_items_options', '`cio_code` = "itemImage"', 'inner');
            $this->db->join('catalog_items_params', '`ci_id` = `cip_ci_id` and cip_cio_id = cio_id and `cip_value`!=""', 'inner');

            if ($this->db->where) {
                $this->db->where .= ' AND ';
            } else {
                $this->db->where = 'WHERE ';
            }
            $this->db->where .= '  
  ((SELECT COALESCE(sum(`ciw_count`),0) FROM inji_catalog_item_warehouses iciw WHERE iciw.ciw_ci_id = ci_id)-
  (SELECT COALESCE(sum(ewb_count) ,0)
  FROM inji_ecommerce_warehouses_block iewb 
  inner JOIN inji_catalog_carts icc ON icc.cc_id = iewb.ewb_cc_id AND (
  (`cc_warehouse_block` = 1 and `cc_status` in(2,3,6)) || 
  (`cc_status` in(0,1) and `cc_date_last_activ` >=subdate(now(),INTERVAL 30 MINUTE))
  )
  WHERE iewb.ewb_ci_id = ci_id))>0 ';
        }

        $this->db->cols = 'count(DISTINCT ci_id) as `count`';

        $count = $this->db->select('catalog_items')->fetch_assoc();
        return $count['count'];
    }

    function update_item_params($ci_id, $params = array()) {
        if ($params) {
            $cio_ids = implode(',', array_keys($params));
            //var_dump($params);
            $this->db->where('cip_ci_id', $ci_id);
            $this->db->where('cip_cio_id', $cio_ids, 'IN');
            $this->db->delete('catalog_items_params');

            foreach ($params as $cio_id => $value) {
                $this->db->insert('catalog_items_params', array('cip_cio_id' => $cio_id, 'cip_ci_id' => $ci_id, 'cip_value' => $value));
            }
        }
        //$item = $this->get_item( $ci_id );
    }

    function get_item($ci_id = '') {
        $this->db->where('ci_id', $ci_id);
        return $this->db->select('catalog_items')->fetch_assoc();
    }

    function del_item($ci_id = '') {
        $this->db->where('ci_id', $ci_id);
        return $this->db->delete('catalog_items');
    }

    function get_item_params($ci_id = '', $key = 'cio_code') {
        //$item = $this->get_item($ci_id);
        //$this->db->cols('`' . $this->db->table_prefix . 'catalog_items_options`.*, `' . $this->db->table_prefix . 'catalog_items_params`.*');
        // $this->db->where('cor_catalog_id', $item['ci_catalog_id']);
        $this->db->join('catalog_items_options', '`cio_id` = `cip_cio_id`');
        $this->db->where('cip_ci_id', $ci_id);
        return $this->db->result_array($this->db->select('catalog_items_params'), $key);
    }

    function get_item_search_list($parent = '', $start = 0, $count = 20, $s) {
        if ($parent !== '')
            $this->db->where('ci_cc_id', $parent, 'IN');
        $this->db->group('ci_product');
        $this->db->join('catalog_item_types', '`cit_id` = `ci_type`');
        $this->db->where('cit_name', '%' . $s . '%', 'LIKE');
        $this->db->where('ci_name', '%' . $s . '%', 'LIKE', 'OR');
        $this->db->limit($start, $count);
        $items = $this->db->result_array($this->db->select('catalog_items'), 'ci_id');
        //echo $this->db->last_query;
        foreach ($items as $key => $item) {
            $items[$key]['ci_images'] = json_decode($item['ci_images'], true);
            $items[$key]['ci_prices'] = json_decode($item['ci_prices'], true);
        }
        return $items;
    }

    function get_item_by_id($ci_id = '') {
        $this->db->where('ci_id', $ci_id);
        $item = $this->db->select('catalog_items')->fetch_assoc();
        $item['type'] = $this->Ecommerce->get_item_type($item['ci_type']);
        $item['ci_images'] = json_decode($item['ci_images'], true);
        $item['ci_prices'] = json_decode($item['ci_prices'], true);
        if ($item['ci_metricPackageInfo'])
            $item['ci_metricPackageInfo'] = json_decode($item['ci_metricPackageInfo'], true);
        if ($item['ci_attachments'])
            $item['ci_attachments'] = json_decode($item['ci_attachments'], true);
        return $item;
    }

    function get_item_types() {
        return $this->db->result_array($this->db->select('catalog_item_types'), 'cit_id');
    }

    function get_item_type($cit_id) {
        $this->db->where('cit_id', $cit_id);
        return $this->db->select('catalog_item_types')->fetch_assoc();
    }

    function get_prices($ci_id, $col = 'ciprice_id') {
        $this->db->where('ciprice_ci_id', $ci_id);
        $this->db->order('ciprice_weight');
        return $this->db->result_array($this->db->select('catalog_items_prices'), $col);
    }

    function get_price($ciprice_id, $ci_id = NULL) {
        $this->db->where('ciprice_id', $ciprice_id);
        if (!empty($ci_id))
            $this->db->where('ciprice_ci_id', $ci_id);
        return $this->db->select('catalog_items_prices')->fetch_assoc();
    }

    function add_item_price($data) {
        return $this->db->insert('catalog_items_prices', $data);
    }

    function update_item_price($ciprice_id, $data) {
        $this->db->where('ciprice_id', $ciprice_id);
        return $this->db->update('catalog_items_prices', $data);
    }

    function create_cart($data = array()) {
        if (empty($data['cc_status']))
            $data['cc_status'] = 1;
        return $this->db->insert('catalog_carts', $data);
    }

    function update_cart($cc_id, $data) {
        $this->db->where('cc_id', $cc_id);
        return $this->db->update('catalog_carts', $data);
    }

    function get_cart($cc_id) {
        $this->db->where('cc_id', $cc_id);
        return $this->db->select('catalog_carts')->fetch_assoc();
    }

    function get_carts($user_id = 0) {
        if ($user_id)
            $this->db->where('cc_user_id', $user_id);
        $this->db->cols = '*,(select count(*) from `' . $this->db->table_prefix . 'catalog_cart_items` where `cci_cc_id` = `cc_id` ) as `items_count`';
        $this->db->order('cc_id', 'DESC');
        return $this->db->result_array($this->db->select('catalog_carts'), 'cc_id');
    }

    function add_to_cart($data) {
        return $this->db->insert('catalog_cart_items', $data);
    }

    function get_cart_items($cc_id, $col = 'cci_id') {
        $this->db->where('cci_cc_id', $cc_id);
        return $this->db->result_array($this->db->select('catalog_cart_items'), $col);
    }

    function get_cart_item($cci_id) {
        $this->db->where('cci_id', $cci_id);
        return $this->db->select('catalog_cart_items')->fetch_assoc();
    }

    function update_cart_item($cci_id, $data) {
        $this->db->where('cci_id', $cci_id);
        return $this->db->update('catalog_cart_items', $data);
    }

    function delete_cart_item($cci_id) {
        $this->db->where('cci_id', $cci_id);
        return $this->db->delete('catalog_cart_items');
    }

    function get_user_info($cui_user_id) {
        $this->db->where('cui_user_id', $cui_user_id);
        return $this->db->select('catalogUsers_info')->fetch_assoc();
    }

    function update_user_info($cui_user_id, $data) {
        if ($this->get_user_info($cui_user_id)) {
            $this->db->where('cui_user_id', $cui_user_id);
            return $this->db->update('catalogUsers_info', $data);
        } else {
            $data['cui_user_id'] = $cui_user_id;
            return $this->db->insert('catalogUsers_info', $data);
        }
    }

    function cart_recount($cc_id) {
        $this->db->where('cc_id', $cc_id);
        $cart = $this->db->select('catalog_carts')->fetch_assoc();
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
        $catalog = Catalog::get($catalog_id);
        $ids[] = $catalog_id;
        if ($catalog->catalog_parent_id) {
            $ids = $this->getCatalogParents($catalog->catalog_parent_id, $ids);
        }
        return $ids;
    }

}

?>
