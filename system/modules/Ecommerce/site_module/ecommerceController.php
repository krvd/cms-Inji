<?php

class ecommerceController extends Controller {

    function quickViewAction($ci_id = 0) {
        $item = Item::get((int) $ci_id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->ci_catalog_id;
        $catalog = $item->catalog;

        if (!empty($_POST['message']) && $this->users->cur->user_id) {
            $this->_COMMENTS->add_comment(array('comment_user_id' => $this->users->cur->user_id, 'comment_text' => $_POST['message'], 'comment_page' => 'item/' . $ci_id));
            $this->url->redirect('/ecommerce/view/' . $ci_id . '?' . time());
        }

        $this->db->where('ciw_ci_id', $item->ci_id);
        $this->db->cols = 'sum(ciw_count) as `sum` ';
        $warehouse = $this->db->select('catalog_item_warehouses')->fetch_assoc();

        $this->view->set_title((empty($item->options['3ec57698-662b-11e4-9462-80c16e818121'])) ? $item->ci_name : $item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value);
        $this->view->page('blank', compact('item', 'warehouse', 'active', 'catalog'));
    }

    function cabinetAction() {
        $bread = [];
        $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');
        $bread[] = array('text' => 'Кабинет', 'href' => '/ecommerce/cabinet');
        $this->view->set_title('Кабинет');
        $this->view->page(compact('bread'));
    }

    function buyCardAction() {
        $this->view->set_title('Покупка карты');
        if ($this->users->cur->user_id) {
            $this->db->where('cc_card_buy', 1);
            $this->db->where('cc_user_id', $this->users->cur->user_id);
            $cart = $this->db->select('catalog_carts')->fetch_assoc();
            if ($cart && $cart['cc_status'] == 5) {
                $this->url->redirect('/', 'Вы уже приобрели клубную карту');
            } elseif ($cart) {
                $this->url->redirect('/', 'Заявка на получение уже отправлена, если с вами ещё не связались, вы можете обратиться в горячую службу по номеру указанному в шапке сайта');
            }
        }
        if (!empty($_POST)) {

            $error = false;
            if ((empty($_POST['user_phone'])) && !$this->users->cur->user_phone) {
                $this->msg->add('Укажите ваш номер');
                $error = true;
            }
            if (!$error) {
                if (!$this->users->cur->user_id) {
                    $user_id = $this->Users->registration($_POST);
                    if (!$user_id) {
                        $error = true;
                    } else {
                        $user = User::get($user_id);
                    }
                } else {
                    $user = $this->users->cur;
                }
                if (!$error) {
                    $user = $this->users->cur;

                    $data['cc_user_id'] = $user->user_id;
                    $data['cc_status'] = 2;
                    if (!empty($_POST['user_phone'])) {
                        $data['cc_tel'] = htmlspecialchars($_POST['user_phone']);
                    } else {
                        $data['cc_tel'] = $user->user_phone;
                    }
                    $data['cc_fio'] = $user->user_name;
                    $data['cc_email'] = $user->user_mail;
                    $data['cc_city'] = htmlspecialchars($_POST['city']);
                    $data['cc_street'] = htmlspecialchars($_POST['street']);
                    $data['cc_comment'] = htmlspecialchars($_POST['cc_comment']);
                    $data['cc_date_status'] = date('Y-m-d H:i:s');
                    $data['cc_complete_data'] = date('Y-m-d H:i:s');
                    $cart = new Cart($data);
                    $cart->save();
                    $cart->addCard();

                    $this->users->cur->user_role_id = 4;
                    $this->users->cur->save();

                    $this->url->redirect('/', 'Ваша заявка была отправлена! Через некоторое время с вами свяжутся!', 'success');
                }
            }
        }
        $this->view->page(compact('cart'));
    }

    function autoCompleteAction() {
        $this->db->cols(['ci_name', 'ci_search_index']);
        $items = $this->db->result_array($this->db->select('catalog_items'));
        $return = [];
        foreach ($items as $item) {
            $return[] = ['name' => $item['ci_name'], 'search' => $item['ci_search_index'] . ' ' . $this->tools->translit($item['ci_search_index'])];
        }
        echo json_encode($return);
    }

    function indexAction($catalog_id = 0) {
        $catalog = Catalog::get((int) $catalog_id);

        if ($catalog) {
            $this->url->redirect('/ecommerce/itemList/' . (int) $catalog_id);
        }
        $this->view->page('main', compact('hitItems', 'bestItems'));
    }

    function itemListAction($catalog_id = 0) {
        //filters=%2Fsort%3Dp.sort_order%2Forder%3DASC%2Flimit%3D15%2Fpage%3D2&route=product%2Fcategory&path=1&manufacturer_id=&search=&tag=
        if (empty($_GET['ajax'])) {

            if (!empty($_GET['search'])) {
                if (!empty($_GET['inCatalog'])) {
                    $catalog_id = (int) $_GET['inCatalog'];
                }
                $search = $_GET['search'];
            } else
                $search = '';

            if (!empty($_GET['sort']) && in_array($_GET['sort'], array('best', 'asc', 'desc', 'hit', 'priceasc', 'pricedesc')))
                $sort = $_GET['sort'];
            else
                $sort = 'asc';

            $pages = new Pages($_GET, ['count' => $this->ecommerce->getItemsCount($catalog_id, trim($search)), 'limit' => 16]);
        }
        else {
            $query = [];
            parse_str(substr(str_replace('/', '&', urldecode($_POST['filters'])), 1), $query);
            //echo substr(str_replace('/', '&', $_POST['filters']),1);
            //print_r($query);
            //print_r($_POST);
            foreach ($query as $key => $value) {
                if (strpos($key, '-c') !== false) {
                    $catalog_ids = substr($key, strpos($key, '-c') + 2);
                    //echo $catalog_ids;
                    break;
                }
            }
            if (!empty($_POST['search'])) {
                $search = $_POST['search'];
            } else
                $search = '';

            if (!empty($query['sort']) && in_array($query['sort'], array('best', 'asc', 'desc', 'hit', 'priceasc', 'pricedesc')))
                $sort = $query['sort'];
            else
                $sort = 'asc';

            $pages = new Pages($query, ['count' => $this->ecommerce->getItemsCount(!empty($catalog_ids) ? $catalog_ids : $catalog_id, trim($search)), 'limit' => 16]);
        }
        $catalog_id = (int) $catalog_id;

        if ($catalog_id < 1)
            $catalog_id = '';

        $active = $catalog_id;
        if ($catalog_id)
            $catalog = Catalog::get($catalog_id);

        $bread = [];
        if (!$catalog || !$catalog->catalog_name) {
            $bread[] = array('text' => 'Каталог');
            $this->view->set_title('Каталог');
        } else {
            $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');
            $catalogIds = $this->ecommerce->getCatalogParents($catalog->catalog_id);
            $catalogIds = array_reverse($catalogIds);
            foreach ($catalogIds as $id) {
                $cat = Catalog::get($id);
                $bread[] = array('text' => $cat->catalog_name, 'href' => '/ecommerce/itemList/' . $cat->catalog_id);
            }
            $this->view->set_title($catalog->catalog_name);
        }


        $items = $this->ecommerce->getItems(!empty($catalog_ids) ? $catalog_ids : $catalog_id, $pages->params['start'], $pages->params['limit'], 'ci_id', trim($search), $sort);
        $catalogs = Catalog::get_list();
        $this->view->page(compact('active', 'catalog', 'sort', 'search', 'pages', 'items', 'catalogs', 'bread'));
    }

    function bonusesAction() {
        $this->view->set_title('Начисления');

        $this->view->page(compact('cart'));
    }

    function viewAction($ci_id = '') {
        $item = Item::get((int) $ci_id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->ci_catalog_id;
        $catalog = $item->catalog;

        $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');

        $catalogIds = $this->ecommerce->getCatalogParents($item->ci_catalog_id);
        $catalogIds = array_reverse($catalogIds);
        foreach ($catalogIds as $id) {
            $cat = Catalog::get($id);
            $bread[] = array('text' => $cat->catalog_name, 'href' => '/ecommerce/itemList/' . $cat->catalog_id);
        }
        $bread[] = array('text' => $item->ci_name);

        if (!empty($_POST['message']) && $this->users->cur->user_id) {
            $this->_COMMENTS->add_comment(array('comment_user_id' => $this->users->cur->user_id, 'comment_text' => $_POST['message'], 'comment_page' => 'item/' . $ci_id));
            $this->url->redirect('/ecommerce/view/' . $ci_id . '?' . time());
        }

        $this->db->where('ciw_ci_id', $item->ci_id);
        $this->db->cols = 'sum(ciw_count) as `sum` ';
        $warehouse = $this->db->select('catalog_item_warehouses')->fetch_assoc();

        $this->view->set_title((empty($item->options['3ec57698-662b-11e4-9462-80c16e818121'])) ? $item->ci_name : $item->options['3ec57698-662b-11e4-9462-80c16e818121']->cip_value);
        $this->view->page(compact('item', 'warehouse', 'active', 'catalog', 'bread'));
    }

    function loadIconsAction($catalogId) {
        if (!empty($_GET['search'])) {
            if (!empty($_GET['inCatalog'])) {
                $catalog_id = (int) $_GET['inCatalog'];
            }
            $search = $_GET['search'];
        } else
            $search = '';
        $catalog = Catalog::get((int) $catalogId);
        if (!$catalog) {
            exit();
        }
        $itemsCount = $this->ecommerce->getItemsCount($catalog->catalog_id, '');
        $pages = new Pages($_GET, ['count' => $this->ecommerce->getItemsCount($catalog->catalog_id, $search), 'limit' => 24, 'url' => '/ecommerce/loadIcons/' . $catalog->catalog_id]);
        $items = $this->ecommerce->getItems($catalog->catalog_id, $pages->params['start'], $pages->params['limit'], 'ci_id', $search, 'asc');
        $this->view->widget('vitrin', ['items' => $items, 'catalog' => $catalog, 'itemsCount' => $itemsCount, 'pages' => $pages]);
    }

}

?>
