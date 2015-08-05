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

namespace Ui\ActiveForm;

class Input extends \Object {

    public $form = null;
    public $activeForm = null;
    public $activeFormParams = [];
    public $modelName = '';
    public $colName = '';
    public $colParams = [];

    function draw() {
        $inputName = $this->colName();
        $inputLabel = $this->colLabel();
        $inputOptions = [
            'value' => $this->value(),
            'disabled' => $this->readOnly()
        ];
        $preset = $this->preset();
        if ($preset !== null) {
            $inputOptions['disabled'] = true;
            $this->form->input('hidden', $inputName, '', $inputOptions);
            return true;
        }
        $classPath = explode('\\', get_called_class());
        $inputType = lcfirst(array_pop($classPath));
        $this->form->input($inputType, $inputName, $inputLabel, $inputOptions);
        return true;
    }

    function parseRequest($request) {
        if (isset($request[$this->colName])) {
            $this->activeForm->model->{$this->colName} = $request[$this->colName];
        } else {
            $this->activeForm->model->{$this->colName} = 0;
            $this->activeForm->model->{$this->colName} = '';
        }
    }

    function value() {
        $value = isset($this->colParams['default']) ? $colParams['default'] : '';
        if ($this->activeForm) {
            $value = ($this->activeForm && $this->activeForm->model && isset($this->activeForm->model->{$this->colName})) ? $this->activeForm->model->{$this->colName} : $value;
        }
        return $value;
    }

    function preset() {
        $preset = !empty($this->activeForm->form['preset'][$this->colName]) ? $this->activeForm->form['preset'][$this->colName] : [];
        if (!empty($this->activeForm->form['userGroupPreset'][\Users\User::$cur->group_id][$this->colName])) {
            $preset = array_merge($preset, $this->activeForm->form['userGroupPreset'][\Users\User::$cur->group_id][$this->colName]);
        }
        if ($preset) {
            $value = '';
            if (!empty($preset['value'])) {
                $value = $preset['value'];
            } elseif (!empty($preset['userCol'])) {
                if (strpos($preset['userCol'], ':')) {
                    $rel = substr($preset['userCol'], 0, strpos($preset['userCol'], ':'));
                    $param = substr($preset['userCol'], strpos($preset['userCol'], ':') + 1);
                    $value = \Users\User::$cur->$rel->$param;
                }
            }
            return $value;
        }
        return null;
    }

    function colName() {
        return "{$this->activeForm->requestFormName}[{$this->activeForm->modelName}][{$this->colName}]";
    }

    function colLabel() {
        $modelName = $this->modelName;
        return ($this->activeForm->model && !empty($modelName::$labels[$this->colName])) ? $modelName::$labels[$this->colName] : $this->colName;
    }

    function readOnly() {
        return !empty($this->activeForm->form['userGroupReadonly'][\Users\User::$cur->group_id]) && in_array($this->colName, $this->activeForm->form['userGroupReadonly'][\Users\User::$cur->group_id]);
    }

}
