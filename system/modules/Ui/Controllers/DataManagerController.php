<?php

/**
 * Data manager controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class DataManagerController extends Controller
{
    function indexAction()
    {
        $result = new Server\Result();
        ob_start();
        $params = [];
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
        }
        if (strpos($_GET['item'], ':')) {
            $raw = explode(':', $_GET['item']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id, $modelName::index(), $params);
        } else {
            $modelName = $_GET['item'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params']['relation'])) {
            $params['relation'] = $_GET['params']['relation'];
            $relations = $modelName::relations();
            $modelName = $relations[$_GET['params']['relation']]['model'];
        }
        $dataManager = new Ui\DataManager($modelName, 'manager');
        $dataManager->draw($params, $model);
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    //function 

    function loadRowsAction()
    {
        $result = new Server\Result();
        $result->content = [];
        ob_start();
        $params = [];
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
        }
        if (strpos($_GET['modelName'], ':')) {
            $raw = explode(':', $_GET['modelName']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id, $modelName::index(), $params);
        } else {
            $modelName = $_GET['modelName'];
            $id = null;
            $model = null;
        }
        if (!empty($_GET['params']['relation'])) {
            $params['relation'] = $_GET['params']['relation'];
            $relations = $modelName::relations();
            $modelName = $relations[$_GET['params']['relation']]['model'];
        }
        if (!empty($_GET['filters'])) {
            $params['filters'] = $_GET['filters'];
        }

        if (!empty($_GET['sortered'])) {
            $params['sortered'] = $_GET['sortered'];
        }
        if (!empty($_GET['mode'])) {
            $params['mode'] = $_GET['mode'];
        }
        if (!empty($_GET['all'])) {
            $params['all'] = true;
        }
        $dataManager = new Ui\DataManager($modelName, $_GET['managerName']);
        if (!empty($_GET['download'])) {
            $params['all'] = true;
            $params['download'] = true;
            set_time_limit(0);
            ob_end_clean();
            header('Content-Encoding: UTF-8');
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=" . $modelName::$objectName . '.csv');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM


            $cols = $dataManager->getCols();
            $cols = array_slice($cols, (!empty($dataManager->managerOptions['groupActions']) ? 1 : 0));
            $endRow = true;
            foreach ($cols as $colName => $options) {
                if (!$endRow) {
                    echo ";";
                }
                $endRow = false;
                echo '"' . $options['label'] . '"';
            }
            echo "\n";
            $endRow = true;
        }
        $rows = $dataManager->getRows($params, $model);
        foreach ($rows as $row) {
            if (!empty($_GET['download'])) {
                $row = array_slice($row, (!empty($dataManager->managerOptions['groupActions']) ? 1 : 0), -1);
                foreach ($row as $col) {
                    if (!$endRow) {
                        echo ";";
                    }
                    $endRow = false;
                    echo '"' . str_replace("\n", '', $col) . '"';
                }
                echo "\n";
                $endRow = true;
            } else {
                Ui\Table::drawRow($row);
            }
        }
        if (!empty($_GET['download'])) {
            exit();
        }
        $result->content['rows'] = ob_get_contents();
        ob_clean();
        $result->content['pages'] = '';
        if (empty($params['all'])) {
            $pages = $dataManager->getPages($params, $model);

            if ($pages) {
                $pages->draw();
            }
            $result->content['pages'] = ob_get_contents();
            ob_end_clean();
        }
        $result->send();
    }

    function loadCategorysAction()
    {
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

    function delRowAction()
    {

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
        $result->successMsg = empty($_GET['silence']) ? 'Запись удалена' : '';
        $result->send();
    }

    function updateRowAction()
    {

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
        $params = [];
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($_GET['params']['relation'])) {
                $relations = $modelName::relations();

                $modelName = $relations[$_GET['params']['relation']]['model'];
            }
        }
        $dataManager = new Ui\DataManager($modelName, $_GET['managerName']);
        if ($dataManager->checkAccess()) {
            $model = $modelName::get($_GET['key'], $modelName::index(), !empty($_GET['params']) ? $_GET['params'] : []);
            if ($model) {
                $model->$_GET['col'] = $_GET['col_value'];
                $model->save($params);
            }
        }
        $result = new Server\Result();
        $result->successMsg = empty($_GET['silence']) ? 'Запись Обновлена' : '';
        $result->send();
    }

    function groupActionAction()
    {


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
            if (!empty($_GET['action']) && !empty($dataManager->managerOptions['groupActions'][$_GET['action']]) && !empty($_GET['ids'])) {
                $action = $dataManager->managerOptions['groupActions'][$_GET['action']];
                switch ($action['action']) {
                    case'delete':
                        $ids = filter_input(INPUT_GET, 'ids', FILTER_SANITIZE_STRING);
                        if ($ids) {
                            $ids = trim($ids, ',');
                            $models = $modelName::getList(['where' => [[$modelName::index(), $ids, 'IN']]]);
                            foreach ($models as $model) {
                                $model->delete();
                            }
                        }
                        break;
                    case 'changeParam':
                        $ids = filter_input(INPUT_GET, 'ids', FILTER_SANITIZE_STRING);
                        if ($ids) {
                            $ids = trim($ids, ',');
                            $models = $modelName::getList(['where' => [[$modelName::index(), $ids, 'IN']]]);
                            foreach ($models as $model) {
                                $model->{$action['col']} = $action['value'];
                                $model->save(!empty($_GET['params']) ? $_GET['params'] : []);
                            }
                        }
                        break;
                    case 'moduleMethod':
                        $ids = filter_input(INPUT_GET, 'ids', FILTER_SANITIZE_STRING);
                        if ($ids) {
                            $comandResult = App::$cur->$action['module']->$action['method']($dataManager, trim($ids, ','), !empty($_GET['adInfo']) ? $_GET['adInfo'] : []);
                        }
                        break;
                }
            }
        }
        $result = new Server\Result();
        if (!empty($comandResult)) {
            $result->success = $comandResult['success'];
            $result->content = $comandResult['content'];
        }
        $result->successMsg = 'Операция выполнена';
        $result->send();
    }

    function delCategoryAction()
    {

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
