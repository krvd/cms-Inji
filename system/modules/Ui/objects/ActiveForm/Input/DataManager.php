<?php

/**
 * Active form input Data Manager
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui\ActiveForm\Input;

class DataManager extends \Ui\ActiveForm\Input
{
    public function draw()
    {
        $inputOptions = [];
        $modelName = $this->modelName;
        $inputOptions['relation'] = $modelName::getRelation($this->colParams['relation']);
        $inputOptions['input'] = $this;
        $this->form->input('dataManager', '', '', $inputOptions);
//        echo '<h4 class=" text-muted">Чтобы добавить связи, сначала создайте объект</h4>';
//echo '<p class=" text-muted">Просто заполните доступные поля и нажмите кнопку внизу формы. После этого дополнительные поля разблокируются</p>';
    }

}
