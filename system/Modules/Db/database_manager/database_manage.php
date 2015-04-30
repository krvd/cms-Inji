<?php
class database_manage extends Inji {
    function __construct() {
        parent::__construct();
        $this->Menu->set_head( 'sidebar', 'Базы данных' );
        $this->Menu->add_item( 'sidebar', 'настроить подключение', '/admin/database_manage/create' );
    }
    function index() {
        $this->content_data['configs'] = $this->Config->module( '_DBROUTER', 'site' );
        $this->view->page();
    }
    function create() {
        $this->content_data['configs'] = $this->Config->module( '_DBROUTER', 'site' );
        if( !empty( $_POST ) ) {
            $this->content_data['configs']['databases'][$_POST['connect_alias']] = $_POST;
            $this->Config->save( 'module', $this->content_data['configs'], '_DBROUTER' );
        }
        $this->view->page();
    }
}
?>