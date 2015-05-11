<?php

class commentsManagerController extends Controller
{

    function indexAction()
    {
        $this->view->set_title('Комментарии');
        $dataTable = new DataTable('Comment', $_GET);
        $this->view->page(compact('dataTable'));
    }

}
?>
