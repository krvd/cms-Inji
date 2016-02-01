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
    public function parseRequest()
    {
        $args = array(
            'params' => [
                'flags' => FILTER_REQUIRE_ARRAY,
            ],
        );
        $return = [];
        $return['params'] = UserRequest::get('params', 'array', []);

        $item = UserRequest::get('modelName', 'string', '');

        if (strpos($item, ':')) {
            $raw = explode(':', $item);
            $return['modelName'] = $raw[0];
            $return['model'] = $return['modelName']::get($raw[1], $return['modelName']::index(), $return['params']);
        } else {
            $return['modelName'] = $item;
            $return['model'] = null;
        }
        if (!empty($return['params']['relation'])) {
            $relation = $return['modelName']::getRelation($return['params']['relation']);
            if (!empty($relation['type']) && $relation['type'] == 'telModlel') {
                $return['modelName'] = $relation['relModel'];
            } else {
                $return['modelName'] = $relation['model'];
            }
        }
        $return['params']['filters'] = UserRequest::get('filters', 'array', []);
        $return['params']['sortered'] = UserRequest::get('sortered', 'array', []);
        $return['params']['mode'] = UserRequest::get('mode', 'string', '');
        $return['params']['all'] = UserRequest::get('all', 'bool', false);

        $return['key'] = UserRequest::get('key', 'int', 0);
        $return['col'] = UserRequest::get('col', 'string', '');
        $return['col_value'] = UserRequest::get('col_value', 'string', '');

        $return['action'] = UserRequest::get('action', 'string', '');
        $return['ids'] = UserRequest::get('ids', 'string', '');
        $return['adInfo'] = UserRequest::get('adInfo', 'array', []);

        $return['download'] = UserRequest::get('download', 'bool', false);
        $return['silence'] = UserRequest::get('silence', 'bool', false);

        $return['managerName'] = UserRequest::get('managerName', 'string', 'manager');
        if (!$return['managerName']) {
            $return['managerName'] = 'manager';
        }

        return $return;
    }

    public function indexAction()
    {
        $result = new Server\Result();

        ob_start();

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);
        $dataManager->draw($request['params'], $request['model']);

        $result->content = ob_get_contents();

        ob_end_clean();

        $result->send();
    }

    public function loadRowsAction()
    {
        $result = new Server\Result();
        $result->content = [];

        ob_start();

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);
        if ($request['download']) {
            $request['params']['all'] = true;
            $request['params']['download'] = true;
            set_time_limit(0);
            ob_end_clean();
            header('Content-Encoding: UTF-8');
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=" . $request['modelName']::$objectName . '.csv');
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
        $rows = $dataManager->getRows($request['params'], $request['model']);
        foreach ($rows as $row) {
            if ($request['download']) {
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
        if ($request['download']) {
            exit();
        }

        $result->content['rows'] = ob_get_contents();
        ob_clean();

        $result->content['pages'] = '';

        if (!$request['params']['all']) {
            $pages = $dataManager->getPages($request['params'], $request['model']);
            if ($pages) {
                $pages->draw();
            }
            $result->content['pages'] = ob_get_contents();
            ob_end_clean();
        }
        $result->send();
    }

    public function loadCategorysAction()
    {
        $result = new Server\Result();

        ob_start();

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);
        $dataManager->drawCategorys();

        $result->content = ob_get_contents();
        ob_end_clean();

        $result->send();
    }

    public function delRowAction()
    {

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);

        if ($dataManager->checkAccess()) {
            $model = $request['modelName']::get($request['key'], $request['modelName']::index(), $request['params']);
            if ($model) {
                $model->delete($request['params']);
            }
        }
        $result = new Server\Result();
        $result->successMsg = empty($request['silence']) ? 'Запись удалена' : '';
        $result->send();
    }

    public function updateRowAction()
    {

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);

        if ($dataManager->checkAccess()) {
            $model = $request['modelName']::get($request['key'], $request['modelName']::index(), $request['params']);
            if ($model) {
                $model->{$request['col']} = $request['col_value'];
                $model->save($request['params']);
            }
        }
        $result = new Server\Result();
        $result->successMsg = empty($request['silence']) ? 'Запись Обновлена' : '';
        $result->send();
    }

    public function groupActionAction()
    {
        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);

        if ($dataManager->checkAccess()) {
            if (!empty($request['action']) && !empty($dataManager->managerOptions['groupActions'][$request['action']]) && trim($request['ids'], ' ,')) {
                $ids = trim($request['ids'], ' ,');
                $action = $dataManager->managerOptions['groupActions'][$request['action']];
                switch ($action['action']) {
                    case'delete':
                        $models = $request['modelName']::getList(['where' => [[$request['modelName']::index(), $ids, 'IN']]]);
                        foreach ($models as $model) {
                            $model->delete();
                        }
                        break;
                    case 'changeParam':
                        $models = $request['modelName']::getList(['where' => [[$request['modelName']::index(), $ids, 'IN']]]);
                        foreach ($models as $model) {
                            $model->{$action['col']} = $action['value'];
                            $model->save(!empty($_GET['params']) ? $_GET['params'] : []);
                        }
                        break;
                    case 'moduleMethod':
                        $comandResult = App::$cur->{$action['module']}->{$action['method']}($dataManager, $ids, $request['adInfo']);
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

    public function delCategoryAction()
    {

        $request = $this->parseRequest();

        $dataManager = new Ui\DataManager($request['modelName'], $request['managerName']);

        if ($dataManager->checkAccess() && !empty($dataManager->managerOptions['categorys'])) {
            $categoryModel = $dataManager->managerOptions['categorys']['model'];
            $model = $categoryModel::get($request['key'], $categoryModel::index(), $request['params']);
            if ($model) {
                $model->delete($request['params']);
            }
        }
        $result = new Server\Result();
        $result->send();
    }

}
