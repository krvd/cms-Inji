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

    public static $cur = null;
    public $moduleName = '';
    public $config = [];
    public $info = [];
    public $params = [];
    public $path = '';
    public $app = null;

    function __construct() {
        $this->moduleName = get_class($this);
        $this->path = Router::getLoadedClassPath($this->moduleName);
        $this->info = $this->getInfo();
        $this->config = Config::module($this->moduleName, !empty($this->info['systemConfig']), $this->app);
    }

    static function resolveModule($app) {
        $moduleName = false;
        if (!empty($app->params[0]) && $app->{$app->params[0]}) {
            $module = $app->{$app->params[0]};
            $module->params = array_slice($app->params, 1);
            $module->app = $app;
            return $module;
        }
        if (!empty($app->config['defaultModule']) && $app->{$app->config['defaultModule']}) {
            $module = $app->{$app->config['defaultModule']};
            $module->params = $app->params;
            $module->app = $app;
            return $module;
        }
        
        if ($app->Main) {
            $module = $app->main;
            $module->params = $app->params;
            $module->app = $app;
            return $module;
        }
        return null;
    }

    function findController() {
        $controllersPath = $this->path . '/' . $this->app->type . 'Controllers';
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
        $controllersPath = $this->path . '/Controllers';
        if (!empty($this->params[0]) && file_exists($controllersPath . '/' . $this->params[0] . 'Controller.php')) {
            include $controllersPath . '/' . $this->params[0] . 'Controller.php';
            $controllerName = $this->params[0] . 'Controller';
            $controller = new $controllerName();
            $controller->params = array_slice($this->params, 1);
            $controller->module = $this;
            $controller->path = $controllersPath;
            $controller->name = $this->params[0];
            return $controller;
        };
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

    static function getInfo($moduleName = '') {
        if (!$moduleName && get_called_class()) {
            $moduleName = get_called_class();
        } elseif(!$moduleName) {
            return [];
        }
        
        if (file_exists(INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/info.php')) {
            return include INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/info.php';
        }
        return [];
    }

}
