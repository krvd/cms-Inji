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

    function begin($header = '',$options=[]) {
        $params = compact('header','options');
        $params['form'] = $this;
        \App::$cur->view->widget('Ui\Form/begin', $params);
    }

    function input($type, $name, $label = '', $options = []) {
        switch ($type) {
            case 'textarea':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/textarea', $params);
                break;
            case 'select':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/select', $params);                
                break;
            default :
                $params = compact('type', 'name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/input', $params);
        }
    }

    function end($btnText = 'Отправить', $attributs = []) {
        $params = compact('btnText', 'attributs');
        $params['form'] = $this;
        \App::$cur->view->widget('Ui\Form/end', $params);
    }

}
