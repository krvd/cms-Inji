<?php

/**
 * Active form controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class ActiveFormController extends Controller
{
    function searchAction()
    {
        $result = new Server\Result();
        if (empty($_GET['search'])) {
            $result->content = [];
            $result->send();
        }
        try {
            if (empty($_GET['modelName'])) {
                throw new Exception('Не указана модель');
            }
            $model = new $_GET['modelName'];
            if (!$model || !is_subclass_of($model, 'Model')) {
                throw new Exception('Модель не найдена');
            }
            if (empty($_GET['formName'])) {
                throw new Exception('Ну казано название формы');
            }
            if (empty($_GET['modelName']::$forms[$_GET['formName']])) {
                throw new Exception('Не существует указанной формы');
            }
            $activeForm = new Ui\ActiveForm($model, $_GET['formName']);
            $inputs = $activeForm->getInputs();
            if (empty($inputs[$_GET['inputName']])) {
                throw new Exception('У формы нет такого поля');
            }
        } catch (Exception $exc) {
            $result->success = false;
            $result->content = $exc->getMessage();
            $result->send();
        }
        $options = [
            'where' => [
            ]
        ];
        $search = [];
        $first = true;
        $searchStr = preg_replace('![^A-zА-я0-9@-_\. ]!iSu', ' ', urldecode($_GET['search']));
        $searchArr = [];
        $colWhere = [];
        foreach (explode(' ', $searchStr) as $part) {
            $colWhere = [];
            $first = true;
            foreach ($inputs[$_GET['inputName']]['cols'] as $col) {
                $part = trim($part);
                if ($part && strlen($part) > 2) {
                    $colWhere[] = [$col, '%' . $part . '%', 'LIKE', $first ? 'AND' : 'OR'];
                    $first = false;
                }
            }
            if ($colWhere) {
                $searchArr[] = $colWhere;
            }
        }
        if ($searchArr) {
            $options['where'][] = $searchArr;
        } else {
            $result->content = [];
            $result->send();
        }
        $options['where'][] = $search;
        $list = $activeForm->getOptionsList($inputs[$_GET['inputName']], ['noEmptyValue' => true], $_GET['modelName'], 'aditional', $options);
        $result->content = $list;
        $result->send();
    }

}
