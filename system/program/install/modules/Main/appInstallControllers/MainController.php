<?php

/**
 * Install controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MainController extends Controller {

    function indexAction() {
        $this->view->page();
    }

    function modulesAction() {
        if (!empty($_GET['modules'])) {
            foreach ($_GET['modules'] as $module) {
                $this->modules->install($module);
            }
            Tools::redirect('/install/main/modules', 'Моудли ' . implode(',', $_GET['modules']) . ' установлены');
        }
        $this->view->page();
    }

    function finishAction() {
        $config = Config::share();
        $config['installed'] = true;
        Config::save('share', $config);
        Tools::redirect('/admin/users/login', 'Система установлена');
    }

}
