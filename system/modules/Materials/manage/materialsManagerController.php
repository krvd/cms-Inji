<?php

class materialsManagerController extends Controller
{

    function indexAction()
    {
        $this->view->set_title('Материалы');
        $dataTable = new DataTable('Material', $_GET);
        $this->view->page(compact('dataTable'));
    }

    function get_list_ajaxAction()
    {
        echo json_encode(Material::get_list(array('array' => true)));
    }

}

?>
