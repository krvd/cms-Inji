<?php

/**
 * Active form input dynamic type
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui\ActiveForm\Input;

class DynamicType extends \Ui\ActiveForm\Input
{
    function draw()
    {
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
        $this->form->input('text', $inputName, $inputLabel, $inputOptions);
        return true;
    }

}
