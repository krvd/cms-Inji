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
    public $userDataTree = [];
    public $options = ['widgetsDir' => 'Form'];

    function __construct($options = []) {
        $this->options = array_merge($this->options, $options);
        $this->genUserDataTree($_POST);
    }

    function genUserDataTree($data, $treeKey = '') {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $this->genUserDataTree($item, $treeKey ? $treeKey . "[{$key}]" : $key);
            } else {
                $this->userDataTree[$treeKey ? $treeKey . "[{$key}]" : $key] = $item;
            }
        }
    }

    function begin($header = '', $options = []) {
        $params = compact('header', 'options');
        $params['form'] = $this;
        \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/begin', $params);
    }

    function input($type, $name, $label = '', $options = []) {
        switch ($type) {
            case 'checkbox':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/checkbox', $params);
                break;
            case 'password':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/password', $params);
                break;
            case 'dynamicList':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/dynamicList', $params);
                break;
            case 'textarea':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/textarea', $params);
                break;
            case 'html':
                \App::$cur->libs->loadLib('ckeditor');
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                if (empty($params['options']['class'])) {
                    $params['options']['class'] = 'htmleditor';
                } else {
                    $params['options']['class'] .= ' htmleditor';
                }
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/textarea', $params);
                break;
            case 'image':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/image', $params);
                break;
            case 'file':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/file', $params);
                break;
            case 'dateTime':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/dateTime', $params);
                break;
            case 'date':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/date', $params);
                break;
            case 'map':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->libs->loadLib('yandexMap');
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/map', $params);
                break;
            case 'select':
                $params = compact('name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/select', $params);
                break;
            default :
                $params = compact('type', 'name', 'label', 'options');
                $params['form'] = $this;
                \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/input', $params);
        }
    }

    function end($btnText = 'Отправить', $attributs = []) {
        $params = compact('btnText', 'attributs');
        $params['form'] = $this;
        \App::$cur->view->widget('Ui\\' . $this->options['widgetsDir'] . '/end', $params);
    }

}
