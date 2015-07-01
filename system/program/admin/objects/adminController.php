<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

/**
 * Description of adminController
 *
 * @author inji
 */
class adminController extends Controller {

    function indexAction() {
        $args = func_get_args();
        call_user_func_array([$this, 'dataManagerAction'], $args);
    }

    function dataManagerAction($model = '', $dataManager = 'manager') {
        if (!$model) {
            $modulePath = Module::getModulePath($this->module->moduleName);
            $path = $modulePath . '/models';
            if (file_exists($path)) {
                $files = array_slice(scandir($path), 2);
                foreach ($files as $file) {
                    if (is_dir($path . '/' . $file)) {
                        continue;
                    }
                    $model = pathinfo($file, PATHINFO_FILENAME);
                    break;
                }
            }
        }
        $fullModelName = $this->module->moduleName . '\\' . ucfirst($model);
        $dataManager = new Ui\DataManager($fullModelName, $dataManager);
        $this->view->setTitle($fullModelName::$objectName);
        $this->view->page(['module' => 'Ui', 'content' => 'dataManager/manager', 'data' => compact('dataManager')]);
    }

    function viewAction($model, $pk) {
        $fullModelName = $this->module->moduleName . '\\' . ucfirst($model);
        $item = $fullModelName::get($pk);
        $this->view->setTitle($item->name());
        if (!empty($_POST['comment'])) {
            $comment = new Dashboard\Comment();
            $comment->text = $_POST['comment'];
            $comment->user_id = \Users\User::$cur->id;
            $comment->model = $fullModelName;
            $comment->item_id = $item->pk();
            $comment->save();
            Tools::redirect($_SERVER['REQUEST_URI']);
        }
        $moduleName = $this->module->moduleName;
        $pageParam = ['module' => 'Ui', 'content' => 'dataManager/view', 'data' => compact('item', 'moduleName')];
        if (isset($_GET['print'])) {
            $pageParam['template'] = 'print';
        }
        $this->view->page($pageParam);
    }

}
