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
    public $action = "";

    function __construct($model) {
        $this->model = $model;
    }

    function checkRequest($formName = 'manager', $params = [], $ajax = false) {
        $modelName = get_class($this->model);
        if (!empty($_POST["ActiveForm_{$formName}"][$modelName])) {
            $request = $_POST["ActiveForm_{$formName}"][$modelName];
            $formOptions = $modelName::$forms[$formName];
            foreach ($formOptions['inputs'] as $col => $params) {
                if (is_string($params)) {
                    $this->model->$col = $request[$col];
                }
            }
            $this->model->save();
            \Inji::app()->SystemMessages->add($this->model->pk() ? 'Новый элемент был успешно добавлен' : 'Изменнеия были успешно сохранены', 'success');
            \Inji::app()->SystemMessages->show();
        }
    }

    function draw($formName = 'manager', $params = []) {
        $modelName = get_class($this->model);
        $form = new Form();
        $form->action = $this->action;
        $form->begin('Создание ' . $modelName, ['onsubmit' => 'ui.form.submitAjax(this);return false;']);
        $formOptions = $modelName::$forms[$formName];
        foreach ($formOptions['map'] as $row) {
            $colSize = 12 / count($row);
            echo "<div class ='row'>";
            foreach ($row as $col) {
                echo "<div class = 'col-xs-{$colSize}'>";
                if (is_string($formOptions['inputs'][$col])) {
                    $type = $formOptions['inputs'][$col];
                } else {
                    $type = $formOptions['inputs'][$col]['type'];
                }
                $form->input($type, "ActiveForm_{$formName}[$modelName][{$col}]", !empty($modelName::$labels[$col]) ? $modelName::$labels[$col] : $col, ['value' => $this->model->$col]);
                echo '</div>';
            }
            echo '</div>';
        }

        $form->end($this->model->pk() ? 'Сохранить' : 'Создать');
    }

}
