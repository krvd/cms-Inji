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
    public $parent = null;

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
        $modeName = $this->modelName;

        if (!empty($modeName::$objectName)) {
            $this->header = $modeName::$objectName;
        } else {
            $this->header = $this->modelName;
        }
    }

    function getInputs() {
        $inputs = [];
        $modelName = $this->modelName;
        foreach ($this->form['map'] as $row) {
            foreach ($row as $col) {
                if (!$col) {
                    continue;
                }
                if (strpos($col, 'form:') === 0) {
                    $colPath = explode(':', $col);
                    if ($this->model->{$colPath[1]}) {
                        $inputs[$col] = new ActiveForm($this->model->{$colPath[1]}, $colPath[2]);
                    } else {
                        $relOptions = $modelName::getRelationOptions($colPath[1]);
                        if (!isset($this->model->_params[$modelName::index()])) {
                            $this->model->_params[$modelName::index()] = 0;
                        }
                        $relOptions['model']::fixPrefix($relOptions['col']);
                        $inputs[$col] = new ActiveForm(new $relOptions['model']([ $relOptions['col'] => &$this->model->_params[$modelName::index()]]), $colPath[2]);
                    }
                    $inputs[$col]->parent = $this;
                } else {
                    $inputs[$col] = $modelName::$cols[$col];
                }
            }
        }
        return $inputs;
    }

    function checkRequest($params = [], $ajax = false) {
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $modelName = $this->modelName;
        if (!empty($_POST[$this->requestFormName][$this->modelName])) {
            $request = $_POST[$this->requestFormName][$this->modelName];
            if ($this->model) {
                $afterSave = [];
                foreach ($this->form['inputs'] as $col => $param) {
                    if (is_object($param)) {
                        $afterSave[] = $param;
                        continue;
                    }
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
                            } else {
                                switch ($param['type']) {
                                    case 'checkbox':
                                    case 'number':
                                        $this->model->$col = 0;
                                        break;
                                    default :
                                        $this->model->$col = '';
                                }
                            }
                            break;
                    }
                }

                \Msg::add($this->model->pk() ? 'Изменнеия были успешно сохранены' : 'Новый элемент был успешно добавлен', 'success');
                $this->model->save(!empty($params['dataManagerParams']) ? $params['dataManagerParams'] : []);
                foreach ($afterSave as $form) {
                    $form->checkRequest();
                }
                if ($ajax) {
                    \Msg::show();
                }
            }
            if (!is_array($params) && is_callable($params)) {
                $params($request);
            }
        }
    }

    function draw($params = [], $ajax = false) {
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $form = new Form();
        if ($this->parent === null) {
            $form->action = $this->action;
            $form->begin($this->header, ['onsubmit' => $ajax ? 'inji.Ui.forms.submitAjax(this);return false;' : '']);
        } else {
            echo "<h3>{$this->header}</h3>";
        }
        foreach ($this->form['map'] as $row) {
            $colSize = 12 / count($row);
            echo "<div class ='row'>";
            foreach ($row as $col) {
                echo "<div class = 'col-xs-{$colSize}'>";
                if ($col) {
                    $this->drawCol($col, $this->form['inputs'][$col], $form, $params);
                }
                echo '</div>';
            }
            echo '</div>';
        }
        if ($this->parent === null) {
            $form->end($this->model ? ($this->model->pk() ? 'Сохранить' : 'Создать') : 'Отправить');
        }
    }

    function drawCol($colName, $options, $form, $params = []) {
        $modelName = $this->modelName;
        if (is_object($options)) {
            $options->draw();
        } else {
            $inputOptions = [
                'value' => $value = isset($options['default']) ? $options['default'] : ''
            ];
            $inputOptions['value'] = ($this->model && isset($this->model->$colName)) ? $this->model->$colName : $inputOptions['value'];

            if ($options['type'] == 'image' && $inputOptions['value']) {
                $inputOptions['value'] = \Files\File::get($inputOptions['value'])->path;
            }
            if ($options['type'] == 'select') {
                $inputOptions['values'] = $this->getOptionsList($options, $params);
            }
            switch ($options['type']) {
                case 'bool';
                    $type = 'checkbox';
                    break;
                default :
                    $type = $options['type'];
            }
            if ($type == 'map') {
                $inputOptions['value'] = [
                    'lat' => $this->model ? $this->model->{$colName . '_lat'} : 0,
                    'lng' => $this->model ? $this->model->{$colName . '_lng'} : 0,
                ];
            }

            $form->input($type, "{$this->requestFormName}[$this->modelName][{$colName}]", ($this->model && !empty($modelName::$labels[$colName])) ? $modelName::$labels[$colName] : $colName, $inputOptions);
        }
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
                $items = $relations[$inputParams['relation']]['model']::getList();
                $values = [0 => 'Не задано'];
                foreach ($items as $key => $item) {
                    if (!empty($inputParams['showCol'])) {
                        $values[$key] = $item->$inputParams['showCol'];
                    } else {
                        $values[$key] = $item->name();
                    }
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
    function checkAccess() {
        if (empty($this->form)) {
            $this->drawError('"' . $this->modelName . '" manager with name: "' . $this->managerName . '" not found');
            return false;
        }
        if ($this->model && !empty($this->form['options']['access']['self']) && \Users\User::$cur->id == $this->model->user_id) {
            return true;
        }
        if (!empty($this->form['options']['access']['groups']) && !in_array(\Users\User::$cur->group_id, $this->form['options']['access']['groups'])) {
            return false;
        }
        return true;
    }

}
