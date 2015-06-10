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
        $dataManager = new Ui\DataManager($modelName);
        $dataManager->draw('manager', $params, $model);
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function loadRowsAction() {
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
        $dataManager = new Ui\DataManager($modelName);
        $rows = $dataManager->getRows($_GET['managerName'], $params, $model);
        foreach ($rows as $row) {
            Ui\Table::drawRow($row);
        }
        $result->content = ob_get_contents();
        ob_end_clean();
        $result->send();
    }

    function delRowAction() {
        $model = $_GET['modelName']::get($_GET['key'], $_GET['modelName']::index(), !empty($_GET['params']) ? $_GET['params'] : []);
        if ($model) {
            $model->delete(!empty($_GET['params']) ? $_GET['params'] : []);
        }
        $result = new Server\Result();
        $result->send();
    }

}
