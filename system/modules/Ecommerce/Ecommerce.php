<?php

class Ecommerce extends Module
{
    function init()
    {
        App::$primary->view->customAsset('js', '/moduleAsset/Ecommerce/js/cart.js');
    }

    function parseFields($data, $cart)
    {
        $fields = \Ecommerce\UserAdds\Field::getList();
        $name = '';
        foreach ($fields as $field) {
            if ($field->save && !empty($data[$field->id])) {
                $name .= htmlspecialchars($data[$field->id]) . ' ';
            }
        }
        $name = trim($name);

        $userAdds = Ecommerce\UserAdds::get([['user_id', $cart->user->id], ['name', $name]]);
        if (!$userAdds) {
            $userAdds = new Ecommerce\UserAdds();
            $userAdds->user_id = $cart->user->id;
            $userAdds->name = $name;
            $userAdds->save();
            foreach ($fields as $field) {
                if (!$field->save) {
                    continue;
                }
                $userAddsValue = new Ecommerce\UserAdds\Value();
                $userAddsValue->value = htmlspecialchars($data[$field->id]);
                $userAddsValue->useradds_field_id = $field->id;
                $userAddsValue->useradds_id = $userAdds->id;
                $userAddsValue->save();
            }
        }
        $user = \Users\User::get($cart->user_id);
        foreach ($fields as $field) {
            $info = new \Ecommerce\Cart\Info();
            $info->name = $field->name;
            $info->value = htmlspecialchars($data[$field->id]);
            $info->useradds_field_id = $field->id;
            $info->cart_id = $cart->id;
            $info->save();
            $relations = [];
            if ($field->userfield) {
                if (strpos($field->userfield, ':')) {
                    $path = explode(':', $field->userfield);
                    $user->{$path[0]}->{$path[1]} = $info->value;
                    $relations[$path[0]] = $path[0];
                } else {
                    $user->{$field->userfield} = $info->value;
                }
            }
            foreach ($relations as $rel) {
                $user->$rel->save();
            }
            $user->save();
        }
        return $userAdds;
    }

    function getCurCart()
    {
        $cart = false;
        if (!empty($_SESSION['cart']['cart_id'])) {
            $cart = Ecommerce\Cart::get((int) $_SESSION['cart']['cart_id']);
        }
        if (!$cart) {
            $cart = new Ecommerce\Cart();
            $cart->cart_status_id = 1;
            $cart->user_id = Users\User::$cur->id;
            $userCard = \Ecommerce\Card\Item::get(\Users\User::$cur->id, 'user_id');
            if ($userCard) {
                $cart->card_item_id = $userCard->id;
            }
            $cart->save();
            $_SESSION['cart']['cart_id'] = $cart->id;
        }
        return $cart;
    }

    function getBreadcrumb()
    {
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

    function parseOptions($options = [])
    {
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
                    case 'name':
                        $selectOptions['order'][] = ['name', strtolower($direction) == 'desc' ? 'desc' : 'asc'];
                        break;
                    case 'sales':
                        $selectOptions['sales'][] = ['name', strtolower($direction) == 'desc' ? 'desc' : 'asc'];
                        break;
                }
            }
        } elseif (!empty($options['sort'])) {
            //echo $options['sort'];
        }

        if (empty($this->config['view_empty_image'])) {
            $selectOptions['where'][] = ['image_file_id', 0, '!='];
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
    function getItems($options = [])
    {
        $selectOptions = $this->parseOptions($options);
        $items = Ecommerce\Item::getList($selectOptions);
        return $items;
    }

    /**
     * Return count of items with params
     * 
     * @param array $params
     * @return int
     */
    function getItemsCount($options = [])
    {
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
