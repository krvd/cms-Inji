<?php

class MenuController extends adminController {

    function indexAction($appType = 'app') {
        $this->view->setTitle('Меню сайта');
        if (!$appType) {
            $appType = App::$cur->type;
        }
        $this->view->page(['data' => compact('appType')]);
    }

}

?>