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
class DataManagerController extends Controller {

    function indexAction() {
        $result = new Server\Result();
        ob_start();
        if (strpos($_GET['item'], ':')) {
            $raw = explode(':', $_GET['item']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id);
        } else {
            $modelName = $_GET['item'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($params['relation'])) {
                $relations = $modelName::relations();
                $modelName = $relations[$params['relation']]['model'];
            }
        } else {
            $params = [];
        }
        $dataManager = new Ui\DataManager($modelName, 'manager');
        $dataManager->draw($params, $model);
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function loadRowsAction() {
        $result = new Server\Result();
        $result->content = [];
        ob_start();
        if (strpos($_GET['modelName'], ':')) {
            $raw = explode(':', $_GET['modelName']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id);
        } else {
            $modelName = $_GET['modelName'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($params['relation'])) {
                $relations = $modelName::relations();
                $modelName = $relations[$params['relation']]['model'];
            }
        } else {
            $params = [];
        }

        if (!empty($_GET['filters'])) {
            $params['filters'] = $_GET['filters'];
        }
        $dataManager = new Ui\DataManager($modelName, $_GET['managerName']);
        $rows = $dataManager->getRows($params, $model);
        foreach ($rows as $row) {
            Ui\Table::drawRow($row);
        }
        $result->content['rows'] = ob_get_contents();
        ob_clean();
        $pages = $dataManager->getPages($params, $model);

        if ($pages) {
            $pages->draw();
        }
        $result->content['pages'] = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function loadCategorysAction() {
        $result = new Server\Result();
        ob_start();
        if (strpos($_GET['modelName'], ':')) {
            $raw = explode(':', $_GET['modelName']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id);
        } else {
            $modelName = $_GET['modelName'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($params['relation'])) {
                $relations = $modelName::relations();
                $modelName = $relations[$params['relation']]['model'];
            }
        } else {
            $params = [];
        }
        if (!empty($_GET['filters'])) {
            $params['filters'] = $_GET['filters'];
        }
        $dataManager = new Ui\DataManager($modelName, $_GET['managerName']);
        $dataManager->drawCategorys();
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function delRowAction() {

        if (strpos($_GET['modelName'], ':')) {
            $raw = explode(':', $_GET['modelName']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id);
        } else {
            $modelName = $_GET['modelName'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($params['relation'])) {
                $relations = $modelName::relations();

                $modelName = $relations[$params['relation']]['model'];
            }
        } else {
            $params = [];
        }
        $dataManager = new Ui\DataManager($modelName, $_GET['managerName']);
        if ($dataManager->checkAccess()) {
            $model = $modelName::get($_GET['key'], $modelName::index(), !empty($_GET['params']) ? $_GET['params'] : []);
            if ($model) {
                $model->delete(!empty($_GET['params']) ? $_GET['params'] : []);
            }
        }
        $result = new Server\Result();
        $result->successMsg = 'Запись удалена';
        $result->send();
    }

    function delCategoryAction() {

        $dataManager = new Ui\DataManager($_GET['modelName'], $_GET['managerName']);
        if ($dataManager->checkAccess() && !empty($dataManager->managerOptions['categorys'])) {
            $categoryModel = $dataManager->managerOptions['categorys']['model'];
            $model = $categoryModel::get($_GET['key'], $categoryModel::index(), !empty($_GET['params']) ? $_GET['params'] : []);
            if ($model) {
                $model->delete(!empty($_GET['params']) ? $_GET['params'] : []);
            }
        }
        $result = new Server\Result();
        $result->send();
    }

}
