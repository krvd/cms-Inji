<?php

/**
 * Modules controller class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class ModulesController extends Controller {

    function indexAction() {
        $this->view->setTitle('Управление модулями');
        $this->view->page();
    }

    function installAction() {
        if (!empty($_GET['modules'])) {
            foreach ($_GET['modules'] as $module) {
                $this->modules->install($module);
            }
            Tools::redirect('/admin/modules', 'Моудли ' . implode(',', $_GET['modules']) . ' установлены');
        }
        $this->view->page();
    }

    function createAction() {
        $codeName = filter_input(INPUT_POST, 'codeName');
        if ($codeName && filter_input(INPUT_POST, 'name')) {
            $codeName = ucfirst($codeName);
            if (file_exists(App::$primary->path . '/modules/' . $codeName . '.php')) {
                Msg::add('Модуль с таким именем уже существует');
            } else {
                $this->modules->createBlankModule(filter_input(INPUT_POST, 'name'), $codeName);
                $config = App::$primary->config;
                $config['modules'][] = $codeName;
                Config::save('app', $config);
                Tools::redirect('/admin/modules', 'Моудль ' . $codeName . ' создан и установлен');
            }
        }
        $this->view->page();
    }

}
