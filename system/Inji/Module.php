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

    function __construct($app) {
        $this->app = $app;
        $this->moduleName = get_class($this);
        $this->path = Router::getLoadedClassPath($this->moduleName);
        $this->info = $this->getInfo();
        $this->config = Config::module($this->moduleName, !empty($this->info['systemConfig']), $this->app);
    }

    static function getModulePaths($moduleName) {
        $moduleName = ucfirst($moduleName);
        $paths = [];
        if (App::$cur !== App::$primary) {
            $paths['primaryAppPath'] = App::$primary->path . '/modules/' . $moduleName;
        }
        $paths['curAppPath'] = App::$cur->path . '/modules/' . $moduleName;
        $paths['systemPath'] = INJI_SYSTEM_DIR . '/modules/' . $moduleName;
        return $paths;
    }

    static function getModulePath($moduleName) {
        $moduleName = ucfirst($moduleName);
        $paths = Module::getModulePaths($moduleName);
        foreach ($paths as $path) {
            if (file_exists($path . '/' . $moduleName . '.php')) {
                return $path;
            }
        }
    }

    static function resolveModule($app) {
        $moduleName = false;
        if (!empty($app->params[0]) && $app->{$app->params[0]}) {
            $module = $app->{$app->params[0]};
            $module->params = array_slice($app->params, 1);
            return $module;
        }
        if (!empty($app->config['defaultModule']) && $app->{$app->config['defaultModule']}) {
            $module = $app->{$app->config['defaultModule']};
            $module->params = $app->params;
            return $module;
        }

        if ($app->Main) {
            $module = $app->Main;
            $module->params = $app->params;
            return $module;
        }
        return null;
    }

    function getControllerPaths() {
        $paths = [];

        if (!empty($this->params[0])) {
            $paths['appTypePath_slice'] = $this->path . '/' . $this->app->type . 'Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
        }
        $paths['appTypePath'] = $this->path . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';

        if (!empty($this->params[0])) {
            $paths['universalPath_slice'] = $this->path . '/Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
        }
        $paths['universalPath'] = $this->path . '/Controllers/' . $this->moduleName . 'Controller.php';

        return $paths;
    }

    function findController() {
        $paths = $this->getControllerPaths();
        foreach ($paths as $pathName => $path) {
            if (file_exists($path)) {
                include $path;
                if (strpos($pathName, 'slice')) {
                    $controllerName = ucfirst($this->params[0]) . 'Controller';
                    $params = array_slice($this->params, 1);
                } else {
                    $controllerName = $this->moduleName . 'Controller';
                    $params = $this->params;
                }
                $controller = new $controllerName();
                $controller->params = $params;
                $controller->module = $this;
                $controller->path = pathinfo($params, PATHINFO_DIRNAME);
                $controller->name = $controllerName;
                return $controller;
            }
        }
    }

    static function getInfo($moduleName = '') {
        if (!$moduleName && get_called_class()) {
            $moduleName = get_called_class();
        } elseif (!$moduleName) {
            return [];
        }
        $paths = Module::getModulePaths($moduleName);
        foreach ($paths as $path) {
            if (file_exists($path . '/info.php')) {
                return include $path . '/info.php';
            }
        }
        return [];
    }

}
