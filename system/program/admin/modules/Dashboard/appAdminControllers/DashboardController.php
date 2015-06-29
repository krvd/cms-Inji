<?php

/**
 * Dashboard controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class DashboardController extends adminController {

    function indexAction() {
        $this->view->setTitle('Панель управления');
        App::$cur->view->page();
    }

}
