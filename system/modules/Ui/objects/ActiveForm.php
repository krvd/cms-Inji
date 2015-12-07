<?php

/**
 * Active form
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui;

class ActiveForm extends \Object
{
    public $model = null;
    public $modelName = '';
    public $header = "";
    public $action = "";
    public $form = [];
    public $inputs = [];
    public $formName = 'noNameForm';
    public $requestFormName = '';
    public $requestFullFormName = '';
    public $parent = null;

    function __construct($model, $form = [])
    {
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
                $this->inputs = $this->getInputs();
            }
        }
        $this->requestFormName = "ActiveForm_{$this->formName}";
        $modeName = $this->modelName;

        if (!empty($this->form['name'])) {
            $this->header = $this->form['name'];
        } elseif (!empty($modeName::$objectName)) {
            $this->header = $modeName::$objectName;
        } else {
            $this->header = $this->modelName;
        }
    }

    function getInputs()
    {
        $inputs = !empty($this->form['inputs']) ? $this->form['inputs'] : [];
        $modelName = $this->modelName;
        foreach ($this->form['map'] as $row) {
            foreach ($row as $col) {
                if (!$col || !empty($inputs[$col])) {
                    continue;
                }
                if (strpos($col, 'form:') === 0) {
                    $colPath = explode(':', $col);
                    if ($this->model->{$colPath[1]}) {
                        $inputs[$col] = new ActiveForm($this->model->{$colPath[1]}, $colPath[2]);
                    } else {
                        $relOptions = $modelName::getRelation($colPath[1]);
                        if (!isset($this->model->_params[$modelName::index()])) {
                            $this->model->_params[$modelName::index()] = 0;
                        }
                        $relOptions['model']::fixPrefix($relOptions['col']);
                        $inputs[$col] = new ActiveForm(new $relOptions['model']([ $relOptions['col'] => &$this->model->_params[$modelName::index()]]), $colPath[2]);
                    }
                    $inputs[$col]->parent = $this;
                } elseif (!empty($modelName::$cols[$col])) {
                    $inputs[$col] = $modelName::$cols[$col];
                }
            }
        }
        return $inputs;
    }

    function checkRequest($params = [], $ajax = false)
    {
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $modelName = $this->modelName;
        $successId = 0;
        if (!empty($_POST[$this->requestFormName][$this->modelName])) {
            $request = $_POST[$this->requestFormName][$this->modelName];
            if ($this->model) {
                $presets = !empty($this->form['preset']) ? $this->form['preset'] : [];
                if (!empty($this->form['userGroupPreset'][\Users\User::$cur->group_id])) {
                    $presets = array_merge($presets, $this->form['userGroupPreset'][\Users\User::$cur->group_id]);
                }
                $afterSave = [];
                $error = false;
                foreach ($this->inputs as $col => $param) {
                    if (!empty($presets[$col])) {
                        continue;
                    }
                    if (is_object($param)) {
                        $afterSave[] = $param;
                        continue;
                    }
                    if (!empty($this->form['userGroupReadonly'][\Users\User::$cur->group_id]) && in_array($col, $this->form['userGroupReadonly'][\Users\User::$cur->group_id])) {
                        continue;
                    }
                    $inputClassName = '\Ui\ActiveForm\Input\\' . ucfirst($param['type']);
                    $input = new $inputClassName();
                    $input->activeForm = $this;
                    $input->activeFormParams = $params;
                    $input->modelName = $this->modelName;
                    $input->colName = $col;
                    $input->colParams = $param;
                    try {
                        $input->validate($request);
                        $input->parseRequest($request);
                    } catch (\Exception $exc) {
                        \Msg::add($exc->getMessage(), 'danger');
                        $error = true;
                    }
                }
                if (!$error) {
                    foreach ($presets as $col => $preset) {
                        if (!empty($preset['value'])) {
                            $this->model->$col = $preset['value'];
                        } elseif (!empty($preset['userCol'])) {
                            if (strpos($preset['userCol'], ':')) {
                                $rel = substr($preset['userCol'], 0, strpos($preset['userCol'], ':'));
                                $param = substr($preset['userCol'], strpos($preset['userCol'], ':') + 1);
                                $this->model->$col = \Users\User::$cur->$rel->$param;
                            } else {
                                $this->model->$col = \Users\User::$cur->{$preset['userCol']};
                            }
                        }
                    }
                    if (!$this->parent) {
                        if (!empty($this->form['successText'])) {
                            $text = $this->form['successText'];
                        } else {
                            $text = $this->model->pk() ? 'Изменения были успешно сохранены' : 'Новый элемент был успешно добавлен';
                        }
                        \Msg::add($text, 'success');
                    }

                    $this->model->save(!empty($params['dataManagerParams']) ? $params['dataManagerParams'] : []);
                    foreach ($afterSave as $form) {
                        $form->checkRequest();
                    }
                    if ($ajax) {
                        \Msg::show();
                    } elseif (!empty($_GET['redirectUrl'])) {
                        \Tools::redirect($_GET['redirectUrl']);
                    }
                    $successId = $this->model->pk();
                }
            }
            if (!is_array($params) && is_callable($params)) {
                $params($request);
            }
        }
        return $successId;
    }

    function draw($params = [], $ajax = false)
    {
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->formName . '"');
            return [];
        }
        $form = new Form(!empty($this->form['formOptions']) ? $this->form['formOptions'] : []);
        \App::$cur->view->widget('Ui\ActiveForm', ['form' => $form, 'activeForm' => $this, 'ajax' => $ajax, 'params' => $params]);
    }

    function drawCol($colName, $options, $form, $params = [])
    {
        $modelName = $this->modelName;
        if (is_object($options)) {
            $options->draw();
        } else {
            $inputClassName = '\Ui\ActiveForm\Input\\' . ucfirst($options['type']);
            $input = new $inputClassName();
            $input->form = $form;
            $input->activeForm = $this;
            $input->activeFormParams = $params;
            $input->modelName = $this->modelName;
            $input->colName = $colName;
            $input->colParams = $options;
            $input->draw();
            return true;
            if (!empty($options['minDate'])) {
                $inputOptions['minDate'] = $options['minDate'];
            }
            if ($type == 'map') {
                $inputOptions['value'] = [
                    'lat' => $this->model ? $this->model->{$colName . '_lat'} : 0,
                    'lng' => $this->model ? $this->model->{$colName . '_lng'} : 0,
                ];
            }
        }
    }

    static function getOptionsList($inputParams, $params = [], $modelName = false, $aditionalInputNamePrefix = 'aditional', $options = [])
    {
        $values = [];
        switch ($inputParams['source']) {
            case 'model':
                $values = $inputParams['model']::getList(['forSelect' => true]);
                break;
            case 'array':
                $values = $inputParams['sourceArray'];
                break;
            case 'method':
                if (!empty($inputParams['params'])) {
                    $values = call_user_func_array([\App::$cur->$inputParams['module'], $inputParams['method']], $inputParams['params']);
                } else {
                    $values = \App::$cur->$inputParams['module']->$inputParams['method']();
                }
                break;
            case 'relation':
                if (!$modelName) {
                    return [];
                }
                $relation = $modelName::getRelation($inputParams['relation']);
                if (!empty($params['dataManagerParams']['appType'])) {
                    $options['appType'] = $params['dataManagerParams']['appType'];
                }
                $items = [];
                if (class_exists($relation['model'])) {
                    $filters = $relation['model']::managerFilters();
                    if (!empty($filters['getRows']['where'])) {
                        $options['where'][] = $filters['getRows']['where'];
                    }
                    if (!empty($relation['order'])) {
                        $options['order'] = $relation['order'];
                    }
                    if (!empty($inputParams['itemName'])) {
                        $options['itemName'] = $inputParams['itemName'];
                    }
                    $items = $relation['model']::getList($options);
                }
                if (!empty($params['noEmptyValue'])) {
                    $values = [];
                } else {
                    $values = [0 => 'Не задано'];
                }
                foreach ($items as $key => $item) {
                    if (!empty($inputParams['showCol'])) {
                        if (is_array($inputParams['showCol'])) {
                            switch ($inputParams['showCol']['type']) {
                                case 'staticMethod':
                                    $values[$key] = $inputParams['showCol']['class']::{$inputParams['showCol']['method']}($item);
                                    break;
                            }
                        } else {
                            $values[$key] = $item->$inputParams['showCol'];
                        }
                    } else {
                        $values[$key] = $item->name();
                    }
                }
                $values = $values;
                break;
        }
        foreach ($values as $key => $value) {
            if (is_array($value) && !empty($value['input']) && empty($value['input']['noprefix'])) {
                $values[$key]['input']['name'] = $aditionalInputNamePrefix . "[{$value['input']['name']}]";
            }
        }
        return $values;
    }

    /**
     * Draw error message
     * 
     * @param text $errorText
     */
    function drawError($errorText)
    {
        echo $errorText;
    }

    /**
     * Check access cur user to form with name in param and $model
     * 
     * @return boolean
     */
    function checkAccess()
    {
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
