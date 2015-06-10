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

    function editorAction($module) {
        if (!file_exists(Module::getModulePath($module) . '/generatorHash.php')) {
            Msg::add('Этот модуль был создан без помощи генератора. Возможности его изменения ограничены и могут привести к порче модуля', 'danger');
        }
        $this->view->page(['data' => compact('module')]);
    }

    function editModelAction($module, $modelName) {
        $path = Modules::getModulePath($module) . '/models/' . $modelName . '.php';
        if (!file_exists($path)) {
            Tools::redirect('/admin/modules/edit/' . $module, 'Модель ' . $modelName . ' не найдена', 'danger');
        }
        include_once Modules::getModulePath($module) . '/models/' . $modelName . '.php';
        $modelFullName = $module . '\\' . $modelName;
        $model = new $modelFullName;
        if (filter_input(INPUT_POST, 'codeName') && filter_input(INPUT_POST, 'name')) {
            $this->modules->generateModel($module, filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'codeName'), [
                'cols' => $_POST['cols']
            ]);
            Tools::redirect('/admin/modules/editor/' . $module, 'Модель ' . filter_input(INPUT_POST, 'codeName') . ' была сохранена');
        }
        $this->view->page(['content' => 'modelEditor', 'data' => compact('module', 'modelName', 'modelFullName', 'model')]);
    }

    function createModelAction($module) {
        if (filter_input(INPUT_POST, 'codeName') && filter_input(INPUT_POST, 'name')) {
            $this->modules->generateModel($module, filter_input(INPUT_POST, 'name'), filter_input(INPUT_POST, 'codeName'), [
                'cols' => $_POST['cols']
            ]);
            Tools::redirect('/admin/modules/editor/' . $module, 'Модель ' . filter_input(INPUT_POST, 'codeName') . ' была создана');
        }
        $this->view->page(['content' => 'modelEditor', 'data' => compact('module')]);
    }
    function delModelAction($module, $modelName){
        unlink(App::$primary->path . '/modules/' . $module . '/models/' . $modelName . '.php');
        $config = Config::custom(App::$primary->path . '/modules/' . $module . '/generatorHash.php');
        if(isset($config['models/' . $modelName . '.php'])){
            unset($config['models/' . $modelName . '.php']);
            Config::save(App::$primary->path . '/modules/' . $module . '/generatorHash.php', $config);
        }
        Tools::redirect('/admin/modules/editor/' . $module, 'Модель ' . $modelName . ' была удалена');
    }

}
