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

namespace Ui;

class ActiveForm extends \Object {

    public $model = null;
    public $header = "";
    public $action = "";
    public $form = [];

    function __construct($model, $form = []) {
        if (is_array($model)) {
            $this->form = $model;
        } else {
            $this->model = $model;
            $this->form = $form;
        }
    }

    function getInputs($modelName) {
        $inputs = [];
        foreach ($this->form['map'] as $row) {
            foreach ($row as $col) {
                $inputs[$col] = $modelName::$cols[$col];
            }
        }
        return $inputs;
    }

    function checkRequest($formName = 'manager', $params = [], $ajax = false) {
        if ($this->model) {
            $modelName = get_class($this->model);
            $this->form = $modelName::$forms[$formName];
            $this->form['inputs'] = $this->getInputs($modelName);
        } else {
            $modelName = $formName;
        }

        if (!empty($_POST["ActiveForm_{$formName}"][$modelName])) {
            $request = $_POST["ActiveForm_{$formName}"][$modelName];
            if ($this->model) {
                foreach ($this->form['inputs'] as $col => $param) {
                    switch ($param['type']) {
                        case 'list':
                            $relations = $modelName::relations();
                            break;
                        default:
                            $this->model->$col = $request[$col];
                            break;
                    }
                }

                \App::$cur->SystemMessages->add($this->model->pk() ? 'Новый элемент был успешно добавлен' : 'Изменнеия были успешно сохранены', 'success');
                $this->model->save(!empty($params['dataManagerParams']) ? $params['dataManagerParams'] : []);
                if ($ajax) {
                    \App::$cur->SystemMessages->show();
                }
            }
            if (!is_array($params) && is_callable($params)) {
                $params($request);
            }
        }
    }

    function draw($formName = 'manager', $params = [], $ajax = true) {
        if ($this->model) {
            $modelName = get_class($this->model);
            if ($this->header === '') {
                $this->header = 'Создание ' . $modelName;
            }
            $this->form = $modelName::$forms[$formName];
            $this->form['inputs'] = $this->getInputs($modelName);
        } else {
            $modelName = $formName;
        }
        $form = new Form();
        $form->action = $this->action;
        $form->begin($this->header, ['onsubmit' => $ajax ? 'inji.Ui.forms.submitAjax(this);return false;' : '']);
        foreach ($this->form['map'] as $row) {
            $colSize = 12 / count($row);
            echo "<div class ='row'>";
            foreach ($row as $col) {
                echo "<div class = 'col-xs-{$colSize}'>";
                $inputOptions = [
                    'value' => $value = isset($this->form['inputs'][$col]['default']) ? $this->form['inputs'][$col]['default'] : ''
                ];
                $inputOptions['value'] = ($this->model && $this->model->pk()) ? $this->model->$col : $inputOptions['value'];

                if ($this->form['inputs'][$col]['type'] == 'select') {
                    $inputOptions['values'] = $this->getOptionsList($this->form['inputs'][$col], $params);
                }

                $form->input($this->form['inputs'][$col]['type'], "ActiveForm_{$formName}[$modelName][{$col}]", ($this->model && !empty($modelName::$labels[$col])) ? $modelName::$labels[$col] : $col, $inputOptions);
                echo '</div>';
            }
            echo '</div>';
        }

        $form->end($this->model ? ($this->model->pk() ? 'Сохранить' : 'Создать') : 'Отправить');
    }

    function getOptionsList($inputParams, $params) {
        $modelName = get_class($this->model);
        switch ($inputParams['source']) {
            case 'array':
                return $inputParams['sourceArray'];
                break;
            case 'method':
                return \App::$cur->$inputParams['module']->$inputParams['method']();
                break;
            case 'relation':

                $relations = $modelName::relations();
                $selectParams = !empty($params['dataManagerParams']) ? $params['dataManagerParams'] : [];
                $items = $relations[$inputParams['relation']]['model']::getList($selectParams);
                $values = [];
                foreach ($items as $key => $item) {
                    $values[$key] = $item->$inputParams['showCol'];
                }
                return $values;
                break;
        }
        return [];
    }

}
