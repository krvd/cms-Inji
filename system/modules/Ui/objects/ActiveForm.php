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
    public $modelName = '';
    public $header = "";
    public $action = "";
    public $form = [];
    public $formName = 'noNameForm';
    public $requestFormName = '';
    public $requestFullFormName = '';

    function __construct($model, $form = []) {
        if (is_array($model)) {
            $this->form = $model;
            if (is_string($form)) {
                $this->formName = $form;
            }
        } else {
            $this->model = $model;
            $this->modelName = get_class($model);
            if (is_array($form)) {
                $this->form = $form;
            } else {
                $this->formName = $form;
                $this->form = \App::$cur->ui->getModelForm($this->modelName, $form);
                $this->form['inputs'] = $this->getInputs();
            }
        }
        $this->requestFormName = "ActiveForm_{$this->formName}";
        $this->header = 'Создание ' . $this->modelName;
    }

    function getInputs() {
        $inputs = [];
        $modelName = $this->modelName;
        foreach ($this->form['map'] as $row) {
            foreach ($row as $col) {
                $inputs[$col] = $modelName::$cols[$col];
            }
        }
        return $inputs;
    }

    function checkRequest($params = [], $ajax = false) {
        if (!$this->chackAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $modelName = $this->modelName;
        if (!empty($_POST[$this->requestFormName][$this->modelName])) {
            $request = $_POST[$this->requestFormName][$this->modelName];
            if ($this->model) {
                foreach ($this->form['inputs'] as $col => $param) {
                    switch ($param['type']) {
                        case 'image':
                            if (!empty($_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col])) {
                                $file_id = \App::$primary->files->upload([
                                    'tmp_name' => $_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col],
                                    'name' => $_FILES[$this->requestFormName]['name'][$this->modelName][$col],
                                    'type' => $_FILES[$this->requestFormName]['type'][$this->modelName][$col],
                                    'size' => $_FILES[$this->requestFormName]['size'][$this->modelName][$col],
                                    'error' => $_FILES[$this->requestFormName]['error'][$this->modelName][$col],
                                ]);
                                if ($file_id) {
                                    $this->model->$col = $file_id;
                                }
                            }
                            break;
                        case 'list':
                            $relations = $modelName::relations();
                            break;
                        default:
                            if (isset($request[$col])) {
                                $this->model->$col = $request[$col];
                            }
                            break;
                    }
                }

                \Msg::add($this->model->pk() ? 'Изменнеия были успешно сохранены' : 'Новый элемент был успешно добавлен', 'success');
                $this->model->save(!empty($params['dataManagerParams']) ? $params['dataManagerParams'] : []);
                if ($ajax) {
                    \Msg::show();
                }
            }
            if (!is_array($params) && is_callable($params)) {
                $params($request);
            }
        }
    }

    function draw($params = [], $ajax = true) {
        if (!$this->chackAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $form = new Form();
        $form->action = $this->action;
        $form->begin($this->header, ['onsubmit' => $ajax ? 'inji.Ui.forms.submitAjax(this);return false;' : '']);
        $modelName = $this->modelName;
        foreach ($this->form['map'] as $row) {
            $colSize = 12 / count($row);
            echo "<div class ='row'>";
            foreach ($row as $col) {
                echo "<div class = 'col-xs-{$colSize}'>";
                $inputOptions = [
                    'value' => $value = isset($this->form['inputs'][$col]['default']) ? $this->form['inputs'][$col]['default'] : ''
                ];
                $inputOptions['value'] = ($this->model) ? $this->model->$col : $inputOptions['value'];

                if ($this->form['inputs'][$col]['type'] == 'image' && $inputOptions['value']) {
                    $inputOptions['value'] = \Files\File::get($inputOptions['value'])->path;
                }
                if ($this->form['inputs'][$col]['type'] == 'select') {
                    $inputOptions['values'] = $this->getOptionsList($this->form['inputs'][$col], $params);
                }

                $form->input($this->form['inputs'][$col]['type'], "{$this->requestFormName}[$this->modelName][{$col}]", ($this->model && !empty($modelName::$labels[$col])) ? $modelName::$labels[$col] : $col, $inputOptions);
                echo '</div>';
            }
            echo '</div>';
        }

        $form->end($this->model ? ($this->model->pk() ? 'Сохранить' : 'Создать') : 'Отправить');
    }

    function drawCol() {
        
    }

    function getOptionsList($inputParams, $params, $modelName = false) {
        if (!$modelName) {
            $modelName = $this->modelName;
        }
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
                $values = [0=>'Не задано'];
                foreach ($items as $key => $item) {
                    $values[$key] = $item->$inputParams['showCol'];
                }
                return $values;
                break;
        }
        return [];
    }

    /**
     * Draw error message
     * 
     * @param text $errorText
     */
    function drawError($errorText) {
        echo $errorText;
    }

    /**
     * Check access cur user to form with name in param and $model
     * 
     * @return boolean
     */
    function chackAccess() {
        if (empty($this->form)) {
            $this->drawError('"' . $this->modelName . '" manager with name: "' . $this->managerName . '" not found');
            return false;
        }

        if (!empty($this->form['options']['access']['groups']) && !in_array(\Users\User::$cur->group_id, $this->form['options']['access']['groups'])) {
            return false;
        }
        return true;
    }

}
