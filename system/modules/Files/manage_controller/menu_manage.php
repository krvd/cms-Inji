<?php

class menu_manage extends Inji {

    function index() {
        $this->content_data['menus'] = $this->menu->get_list();
        $this->view->page();
    }

    function create() {
        if (!empty($_POST['mg_name']) && !empty($_POST['mg_code'])) {
            $this->menu->create_menu(array('mg_name' => $_POST['mg_name'], 'mg_code' => $_POST['mg_code'], 'mg_user_id' => $this->users->cur->user_id));
            Tools::redirect($this->url->up_to(1));
        }
        $this->view->page();
    }

    function items($mg_id) {
        $this->content_data['menu'] = $this->menu->get_menu($mg_id);
        $this->content_data['items'] = $this->menu->get_items($mg_id);
        $this->view->page();
    }

    function add_item($mg_id) {
        if (!empty($_POST['mi_name']) && !empty($_POST['mi_href'])) {
            $this->menu->create_item(array('mi_name' => $_POST['mi_name'], 'mi_href' => $_POST['mi_href'], 'mi_mg_id' => $mg_id));
            Tools::redirect($this->url->up_to(2).'items/'.$mg_id);
        }
        $this->view->page();
    }

    function edit($template) {
        $templates = $this->Config->module('View', 'site');
        $this->content_data['template'] = $templates['install_templates'][$template];
        if (!empty($_POST)) {
            foreach ($_POST['css'] as $key => $item)
                if (empty($item))
                    unset($_POST['css'][$key]);
            $templates['install_templates'][$template]['css'] = $_POST['css'];
            foreach ($_POST['js'] as $key => $item)
                if (empty($item))
                    unset($_POST['js'][$key]);
            $templates['install_templates'][$template]['js'] = $_POST['js'];
            $templates['install_templates'][$template]['favicon'] = $_POST['favicon'];
            $templates['install_templates'][$template]['template_name'] = $_POST['template_name'];
            $templates['current'] = $template;
            $this->Config->save('module', $templates, 'View', 'site');
            Tools::redirect($this->url->up_to(2));
        }
        $this->view->page();
    }

}

?>