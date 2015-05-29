<?php

class MenuController extends Controller {

    function indexAction($appType = false) {
        $this->view->setTitle('Меню сайта');
        if (!$appType) {
            $appType = App::$cur->type;
        }
        //$menus = $this->menu->config[$appType]['menus'];
        $this->view->page(['data' => compact('appType')]);
    }

    function createAction() {
        $this->view->set_title('Создание меню');
        if (!empty($_POST['mg_name']) && !empty($_POST['mg_code'])) {
            $this->menu->create_menu(array('mg_name' => $_POST['mg_name'], 'mg_code' => $_POST['mg_code'], 'mg_user_id' => $this->Users->cur->user_id));
            $this->url->redirect('/admin/Menu');
        }
        $this->view->page();
    }

    function itemsAction($mg_id) {
        $this->view->set_title('Элементы меню');
        $menu = $this->menu->get_menu($mg_id);
        $items = $this->menu->get_items($mg_id);
        $this->view->page(compact('items', 'menu'));
    }

    function add_itemAction($mg_id) {
        $this->view->set_title('Добавить пункт меню');
        if (!empty($_POST['mi_name']) && (!empty($_POST['mi_href']) || !empty($_POST['mi_advance']) )) {
            $_POST['mi_mg_id'] = $mg_id;
            if (!empty($_POST['mi_advance']))
                $_POST['mi_advance'] = json_encode($_POST['mi_advance']);
            $this->menu->create_item($_POST);
            $this->url->redirect('/admin/Menu/items/' . $mg_id);
        }
        $this->view->page();
    }

    function editAction($mi_id) {
        $this->view->set_title('Изменить пункт меню');
        if (!empty($_POST)) {
            if (!empty($_POST['mi_advance']))
                $_POST['mi_advance'] = json_encode($_POST['mi_advance']);
            $this->menu->update_item($mi_id, $_POST);

            $item = $this->menu->get_item($mi_id);
            $this->url->redirect('/admin/Menu/items/' . $item['mi_mg_id']);
        }
        $item = $this->menu->get_item($mi_id);
        if (!empty($item['mi_advance']))
            $item['mi_advance'] = json_decode($item['mi_advance'], true);

        $this->view->page(compact('item'));
    }

    function delAction($mi_id) {
        $this->db->where('mi_id', $mi_id);
        $this->db->delete('menu_items');
        $this->url->redirect($this->url->module());
    }

    function sort_itemsAction() {
        $i = 0;
        print_r($_GET);
        foreach ($_GET as $id) {
            $this->menu->update_item($id, array('mi_weight' => $i++));
        }
    }

}

?>