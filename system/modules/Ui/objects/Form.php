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

class Form extends \Object {

    public $method = 'POST';
    public $action = '';
    public $inputs = [];
    public $map = [];

    function begin($header = '') {
        $params = compact('header');
        $params['form'] = $this;
        \Inji::app()->view->widget('Ui\Form/begin', $params);
    }

    function input($type, $name, $label = '', $options = []) {
        switch ($type) {
            case 'textarea':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \Inji::app()->view->widget('Ui\Form/textarea', $params);
                break;
            default :
                $params = compact('type', 'name', 'label', 'options');
                $params['form'] = $this;
                \Inji::app()->view->widget('Ui\Form/input', $params);
        }
    }

    function end($btnText = 'Отправить', $attributs = []) {
        $params = compact('btnText', 'attributs');
        $params['form'] = $this;
        \Inji::app()->view->widget('Ui\Form/end', $params);
    }

}
