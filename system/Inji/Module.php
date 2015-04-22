<?php

/**
 * Module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Module {

    public $config = [];
    public $params = [];
    public $path = '';

    function __construct() {
        $this->config = Inji::app()->config->module(get_called_class());
    }

    function findController() {
        if (!empty($this->params[0]) && file_exists($this->path . '/Controllers/' . $this->params[0] . 'Controller.php')) {
            include $this->path . '/Controllers/' . $this->params[0] . 'Controller.php';
            $controllerName = $this->params[0] . 'Controller';
            $controller = new $controllerName();
            $controller->params = array_slice($this->params, 1);
            $controller->path = $this->path . '/Controllers/';
            return $controller;
        }
        if (file_exists($this->path . '/Controllers/' . get_called_class() . 'Controller.php')) {
            include $this->path . '/Controllers/' . get_called_class() . 'Controller.php';
            $controllerName = get_called_class() . 'Controller';
            $controller = new $controllerName();
            $controller->params = $this->params;
            $controller->path = $this->path . '/Controllers/';
            return $controller;
        }
    }

}
