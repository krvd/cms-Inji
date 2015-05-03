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

    public $moduleName = '';
    public $config = [];
    public $params = [];
    public $path = '';

    function __construct() {
        $this->moduleName = get_called_class();
        $this->config = Inji::app()->config->module($this->moduleName);
        $this->path = Inji::app()->router->getLoadedClassPath($this->moduleName);
    }

    function findController() {
        $controllersPath = $this->path . '/' . Inji::app()->curApp['type'] . 'Controllers';
        if (!empty($this->params[0]) && file_exists($controllersPath . '/' . $this->params[0] . 'Controller.php')) {
            include $controllersPath . '/' . $this->params[0] . 'Controller.php';
            $controllerName = $this->params[0] . 'Controller';
            $controller = new $controllerName();
            $controller->params = array_slice($this->params, 1);
            $controller->module = $this;
            $controller->path = $controllersPath;
            $controller->name = $this->params[0];
            return $controller;
        }
        if (file_exists($controllersPath . '/' . $this->moduleName . 'Controller.php')) {
            include $controllersPath . '/' . $this->moduleName . 'Controller.php';
            $controllerName = $this->moduleName . 'Controller';
            $controller = new $controllerName();
            $controller->params = $this->params;
            $controller->module = $this;
            $controller->path = $controllersPath;
            $controller->name = $this->moduleName;
            return $controller;
        }
    }

}
