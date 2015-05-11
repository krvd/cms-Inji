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
class UiController extends Controller {

    function dataManagerAction() {
        $return = [];
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
        $dataManager->draw('manager', $model, $params);
        $return['content'] = ob_get_contents();
        ob_end_clean();
        echo json_encode($return);
    }

    function formPopUpAction() {
        $return = [];
        ob_start();
        if (strpos($_GET['item'], ':')) {
            $raw = explode(':', $_GET['item']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id);
        } else {
            $modelName = $_GET['item'];
            $id = null;
            $model = new $modelName();
        }
        if (!empty($_GET['params'])) {
            $params = $_GET['params'];
            if (!empty($params['preset'])) {
                $model->setParams($params['preset']);
            }
        } else {
            $params = [];
        }
        $form = new Ui\ActiveForm($model);
        $form->action = '/admin/ui/formPopUp/?'.http_build_query($_GET);
        $form->checkRequest('manager', $params, true);
        $form->draw('manager', $params);
        $return['content'] = ob_get_contents();
        ob_end_clean();
        echo json_encode($return);
    }

}
