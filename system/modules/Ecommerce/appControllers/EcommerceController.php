<?php

class ecommerceController extends Controller {

    function quickViewAction($id = 0) {
        $item = \Ecommerce\Item::get((int) $id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->category_id;
        $catalog = $item->category;

        $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');

        $catalogIds = $this->ecommerce->getCatalogParents($item->category_id);
        $catalogIds = array_reverse($catalogIds);
        foreach ($catalogIds as $id) {
            $cat = Ecommerce\Category::get($id);
            $bread[] = array('text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id);
        }
        $bread[] = array('text' => $item->name());
        $this->view->setTitle($item->name());
        $this->view->page(['page' => 'blank', 'content' => 'view', 'data' => compact('item', 'active', 'catalog', 'bread')]);
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
        $items = \Ecommerce\Item::getList(['cols' => ['name', 'search_index']]);
        $return = [];
        foreach ($items as $item) {
            $return[] = ['name' => $item->name(), 'search' => $item->search_index];
        }
        echo json_encode($return);
    }

    function indexAction($catalog_id = 0) {
        Tools::redirect('/ecommerce/itemList');
        $catalog = Ecommerce\Category::get((int) $catalog_id);

        if ($catalog) {
            $this->url->redirect('/ecommerce/itemList/' . (int) $catalog_id);
        }
        $this->view->page('main', compact('hitItems', 'bestItems'));
    }

    function itemListAction($category_id = 0) {
        if (!empty($_GET['search'])) {
            if (!empty($_GET['inCatalog'])) {
                $category_id = (int) $_GET['inCatalog'];
            }
            $search = $_GET['search'];
        } else
            $search = '';

        if (!empty($_GET['sort']) && in_array($_GET['sort'], array('best', 'asc', 'desc', 'hit', 'priceasc', 'pricedesc'))) {
            $sort = $_GET['sort'];
        } else {
            $sort = 'asc';
        }

        $pages = new \Ui\Pages($_GET, ['count' => $this->ecommerce->getItemsCount($category_id, trim($search)), 'limit' => 18]);

        $category_id = (int) $category_id;

        if ($category_id < 1)
            $category_id = '';

        $active = $category_id;
        $category = null;
        if ($category_id)
            $category = Ecommerce\Category::get($category_id);

        $bread = [];
        if (!$category || !$category->name) {
            $bread[] = array('text' => 'Каталог');
            $this->view->setTitle('Каталог');
        } else {
            $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');
            $categoryIds = $this->ecommerce->getCatalogParents($category->id);
            $categoryIds = array_reverse($categoryIds);
            foreach ($categoryIds as $id) {
                $cat = Ecommerce\Category::get($id);
                $bread[] = array('text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id);
            }
            $this->view->setTitle($category->name);
        }

        $items = $this->ecommerce->getItems(!empty($category_ids) ? $category_ids : $category_id, $pages->params['start'], $pages->params['limit'], 'id', trim($search), $sort);
        $categorys = Ecommerce\Category::getList();
        $this->view->page(['data' => compact('active', 'category', 'sort', 'search', 'pages', 'items', 'categorys', 'bread')]);
    }

    function viewAction($id = '') {
        $item = \Ecommerce\Item::get((int) $id);
        if (!$item) {
            $this->url->redirect('/ecommerce/', 'Такой товар не найден');
        }
        $active = $item->category_id;
        $catalog = $item->category;

        $bread[] = array('text' => 'Каталог', 'href' => '/ecommerce');

        $catalogIds = $this->ecommerce->getCatalogParents($item->category_id);
        $catalogIds = array_reverse($catalogIds);
        foreach ($catalogIds as $id) {
            $cat = Ecommerce\Category::get($id);
            $bread[] = array('text' => $cat->name, 'href' => '/ecommerce/itemList/' . $cat->id);
        }
        $bread[] = array('text' => $item->name());
        $this->view->setTitle($item->name());
        $this->view->page(['data' => compact('item', 'active', 'catalog', 'bread')]);
    }

}

?>