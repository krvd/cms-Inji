<?php

/**
 * Select input
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui\ActiveForm\Input;

class Select extends \Ui\ActiveForm\Input {

    function draw() {
        $inputName = $this->colName();
        $inputLabel = $this->colLabel();

        $inputOptions = [
            'value' => $this->value(),
            'disabled' => $this->readOnly(),
            'values' => \Ui\ActiveForm::getOptionsList($this->colParams, $this->activeFormParams, $this->activeForm->modelName, $inputName)
        ];
        if (!empty($inputOptions['values'][$this->activeForm->model->{$this->colName}]['input'])) {
            $aditionalCol = $inputOptions['values'][$this->activeForm->model->{$this->colName}]['input']['name'];
            $inputOptions['aditionalValue'] = $this->activeForm->model->$aditionalCol;
        }

        $preset = $this->preset();

        if ($preset !== null) {
            $inputOptions['disabled'] = true;
            $this->form->input('hidden', $inputName, '', $inputOptions);
            return true;
        }
        $this->form->input('select', $inputName, $inputLabel, $inputOptions);
        return true;
    }

}
