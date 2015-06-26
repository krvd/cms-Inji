<?php

/**
 * Ui helper controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class UiController extends Controller {

    function formPopUpAction() {
        if (strpos($_GET['item'], ':')) {
            $raw = explode(':', $_GET['item']);
            $modelName = $raw[0];
            $id = $raw[1];
            $model = $modelName::get($id, $modelName::index(), !empty($_GET['params']['dataManagerParams']) ? $_GET['params']['dataManagerParams'] : []);
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
        $form = new Ui\ActiveForm($model, 'manager');
        $form->action = '/ui/formPopUp/?' . http_build_query($_GET);
        if (!empty($_GET['_'])) {
            $return = new Server\Result();
            ob_start();
            $form->checkRequest($params, true);
            $form->draw($params, true);
            $return->content = ob_get_contents();
            ob_end_clean();
            $return->send();
        } else {
            $form->checkRequest($params, true);
            $this->view->page(['content' => 'form', 'data' => compact('form', 'params')]);
        }
    }

    function fastEditAction() {
        $model = $_POST['model']::get($_POST['key']);
        if ($model && $model->checkAccess()) {
            $model->$_POST['col'] = $_POST['data'];
            $model->save();
        }
    }

}
