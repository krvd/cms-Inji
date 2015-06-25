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

    function begin($header = '', $options = []) {
        $params = compact('header', 'options');
        $params['form'] = $this;
        \App::$cur->view->widget('Ui\Form/begin', $params);
    }

    function input($type, $name, $label = '', $options = []) {
        switch ($type) {
            case 'checkbox':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/checkbox', $params);
                break;
            case 'dynamicList':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/dynamicList', $params);
                break;
            case 'textarea':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/textarea', $params);
                break;
            case 'html':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                if (empty($params['options']['class'])) {
                    $params['options']['class'] = 'htmleditor';
                } else {
                    $params['options']['class'] .= ' htmleditor';
                }
                \App::$cur->view->widget('Ui\Form/textarea', $params);
                break;
            case 'image':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/image', $params);
                break;
            case 'date':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\Form/date', $params);
                break;
            case 'map':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->libs->loadLib('yandexMap');
                \App::$cur->view->widget('Ui\Form/map', $params);
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
