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
                $presets = !empty($this->form['preset']) ? $this->form['preset'] : [];
                if (!empty($this->form['userGroupPreset'][\Users\User::$cur->group_id])) {
                    $presets = array_merge($presets, $this->form['userGroupPreset'][\Users\User::$cur->group_id]);
                }
                $afterSave = [];
                foreach ($this->form['inputs'] as $col => $param) {
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
                    switch ($param['type']) {
                        case 'files':
                            if (!empty($_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col])) {
                                $file_ids = !empty($request[$col]) ? $request[$col] : [];
                                foreach ($_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col] as $key => $tmp_name) {
                                    $file_ids[] = \App::$primary->files->upload([
                                        'tmp_name' => $_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col][$key],
                                        'name' => $_FILES[$this->requestFormName]['name'][$this->modelName][$col][$key],
                                        'type' => $_FILES[$this->requestFormName]['type'][$this->modelName][$col][$key],
                                        'size' => $_FILES[$this->requestFormName]['size'][$this->modelName][$col][$key],
                                        'error' => $_FILES[$this->requestFormName]['error'][$this->modelName][$col][$key],
                                            ], [
                                        'upload_code' => 'activeForm:' . $modelName . ':' . $this->model->pk()
                                    ]);
                                }
                                $this->model->$col = implode(',', array_filter($file_ids));
                            }
                            break;
                        case 'image':
                            if (!empty($_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col])) {
                                $file_id = \App::$primary->files->upload([
                                    'tmp_name' => $_FILES[$this->requestFormName]['tmp_name'][$this->modelName][$col],
                                    'name' => $_FILES[$this->requestFormName]['name'][$this->modelName][$col],
                                    'type' => $_FILES[$this->requestFormName]['type'][$this->modelName][$col],
                                    'size' => $_FILES[$this->requestFormName]['size'][$this->modelName][$col],
                                    'error' => $_FILES[$this->requestFormName]['error'][$this->modelName][$col],
                                        ], [
                                    'upload_code' => 'activeForm:' . $modelName . ':' . $this->model->pk(),
                                    'accept_group' => 'image'
                                ]);
                                if ($file_id) {
                                    $this->model->$col = $file_id;
                                }
                            }
                            break;
                        case 'password':
                            if (!empty($request[$col]['pass']) && !empty($request[$col]['pass'])) {
                                if (empty($request[$col]['pass'])) {
                                    \Msg::add('Вы не ввели пароль в первое поле', 'danger');
                                    continue;
                                }
                                if (empty($request[$col]['repeat'])) {
                                    \Msg::add('Вы не ввели пароль во второе поле', 'danger');
                                    continue;
                                }
                                if ($request[$col]['pass'] != $request[$col]['repeat']) {
                                    \Msg::add('Введенные пароли не совадают', 'danger');
                                    continue;
                                }
                                $this->model->$col = \App::$cur->users->hashpass($request[$col]['pass']);
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

                foreach ($presets as $col => $preset) {
                    if (!empty($preset['value'])) {
                        $this->model->$col = $preset['value'];
                    } elseif (!empty($preset['userCol'])) {
                        if (strpos($preset['userCol'], ':')) {
                            $rel = substr($preset['userCol'], 0, strpos($preset['userCol'], ':'));
                            $param = substr($preset['userCol'], strpos($preset['userCol'], ':') + 1);
                            $this->model->$col = \Users\User::$cur->$rel->$param;
                        }
                    }
                }
                \Msg::add($this->model->pk() ? 'Изменения были успешно сохранены' : 'Новый элемент был успешно добавлен', 'success');
                $this->model->save(!empty($params['dataManagerParams']) ? $params['dataManagerParams'] : []);
                foreach ($afterSave as $form) {
                    $form->checkRequest();
                }
                if ($ajax) {
                    \Msg::show();
                } elseif (!empty($_GET['redirectUrl'])) {
                    \Tools::redirect($_GET['redirectUrl']);
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
        $form = new Form(!empty($this->form['formOptions']) ? $this->form['formOptions'] : []);
        if ($this->parent === null) {
            $form->action = $this->action;
            $form->begin($this->header, ['onsubmit' => $ajax ? 'inji.Ui.forms.submitAjax(this);return false;' : '']);
        } elseif ($this->header) {
            echo "<h3>{$this->header}</h3>";
        }
        if (empty($this->form['noMapCell'])) {
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
        } else {
            foreach ($this->form['map'] as $row) {
                foreach ($row as $col) {
                    if ($col) {
                        $this->drawCol($col, $this->form['inputs'][$col], $form, $params);
                    }
                }
            }
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
            $preset = !empty($this->form['preset'][$colName]) ? $this->form['preset'][$colName] : [];
            if (!empty($this->form['userGroupPreset'][\Users\User::$cur->group_id][$colName])) {
                $preset = array_merge($preset, $this->form['userGroupPreset'][\Users\User::$cur->group_id][$colName]);
            }
            if ($preset) {
                $inputOptions['disabled'] = true;
                if (!empty($preset['value'])) {
                    $inputOptions['value'] = $preset['value'];
                } elseif (!empty($preset['userCol'])) {
                    if (strpos($preset['userCol'], ':')) {
                        $rel = substr($preset['userCol'], 0, strpos($preset['userCol'], ':'));
                        $param = substr($preset['userCol'], strpos($preset['userCol'], ':') + 1);

                        $inputOptions['value'] = \Users\User::$cur->$rel->$param;
                    }
                }
                $form->input('hidden', "{$this->requestFormName}[$this->modelName][{$colName}]", ($this->model && !empty($modelName::$labels[$colName])) ? $modelName::$labels[$colName] : $colName, $inputOptions);
                return;
            }

            if ($options['type'] == 'image' && $inputOptions['value']) {
                $inputOptions['value'] = \Files\File::get($inputOptions['value'])->path;
            }
            if ($options['type'] == 'select') {
                $inputOptions['values'] = $this->getOptionsList($options, $params);
            }
            if (!empty($this->form['userGroupReadonly'][\Users\User::$cur->group_id]) && in_array($colName, $this->form['userGroupReadonly'][\Users\User::$cur->group_id])) {
                $inputOptions['disabled'] = true;
            }
            if (!empty($options['minDate'])) {
                $inputOptions['minDate'] = $options['minDate'];
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
                $filters = $relations[$inputParams['relation']]['model']::managerFilters();
                $items = $relations[$inputParams['relation']]['model']::getList(['where' => !empty($filters['getRows']['where']) ? $filters['getRows']['where'] : '']);

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
