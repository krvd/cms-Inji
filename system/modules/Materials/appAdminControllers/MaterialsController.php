<?php

class MaterialsController extends Controller {

    function indexAction() {
        $this->view->setTitle('Материалы');
        $this->view->page();
    }

    function get_list_ajaxAction() {
        echo json_encode(Material::get_list(array('array' => true)));
    }

}

?>
