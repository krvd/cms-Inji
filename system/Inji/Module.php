<?php

/**
 * Module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Module
{
    public static $cur = null;
    public $moduleName = '';
    public $config = [];
    public $info = [];
    public $params = [];
    public $path = '';
    public $app = null;

    function __construct($app)
    {
        $this->app = $app;
        $this->moduleName = get_class($this);
        $this->path = Router::getLoadedClassPath($this->moduleName);
        $this->info = $this->getInfo();
        $this->config = Config::module($this->moduleName, !empty($this->info['systemConfig']));
    }

    static function getModulePaths($moduleName)
    {
        $moduleName = ucfirst($moduleName);
        $paths = [];
        if (App::$cur !== App::$primary) {
            $paths['primaryAppPath'] = App::$primary->path . '/modules/' . $moduleName;
        }
        $paths['curAppPath'] = App::$cur->path . '/modules/' . $moduleName;
        $paths['systemPath'] = INJI_SYSTEM_DIR . '/modules/' . $moduleName;
        return $paths;
    }

    static function getModulePath($moduleName)
    {
        $moduleName = ucfirst($moduleName);
        $paths = Module::getModulePaths($moduleName);
        foreach ($paths as $path) {
            if (file_exists($path . '/' . $moduleName . '.php')) {
                return $path;
            }
        }
    }

    static function installed($moduleName, $app)
    {

        if (in_array($moduleName, self::getInstalled($app))) {
            return true;
        }

        return FALSE;
    }

    static function getInstalled($app, $primary = null)
    {
        if (!$primary) {
            $primary = App::$primary;
        }
        $system = !empty(Inji::$config['modules']) ? Inji::$config['modules'] : [];
        $primary = !empty($primary->config['modules']) ? $primary->config['modules'] : [];
        $actual = $app !== $primary && !empty($app->config['modules']) ? $app->config['modules'] : [];
        $modules = array_unique(array_merge($system, $primary, $actual));
        return $modules;
    }

    static function resolveModule($app)
    {
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

    function getControllerPaths()
    {
        $paths = [];
        if (App::$cur != App::$primary) {
            if (!empty($this->params[0])) {
                $paths['primaryAppAppTypePath_slice'] = App::$primary->path . '/modules/' . $this->moduleName . '/' . $this->app->type . 'Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
                if (App::$primary->{$this->moduleName}) {
                    $paths['primaryAppAppTypePath_slice'] = App::$primary->{$this->moduleName}->path . '/' . $this->app->type . 'Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
                }
            }
            $paths['primaryAppAppAppTypePath'] = App::$primary->path . '/modules/' . $this->moduleName . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';
            if (App::$primary->{$this->moduleName}) {
                $paths['primaryAppAppTypePath'] = App::$primary->{$this->moduleName}->path . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';
            }
            $paths['curAppAppTypePath'] = $this->app->{$this->moduleName}->path . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';
        }

        if (!empty($this->params[0])) {
            $paths['appAppTypePath_slice'] = $this->app->path . '/modules/' . $this->moduleName . '/' . $this->app->type . 'Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
            $paths['appTypePath_slice'] = $this->path . '/' . $this->app->type . 'Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
        }
        $paths['appAppTypePath'] = $this->app->path . '/modules/' . $this->moduleName . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';
        $paths['appTypePath'] = $this->path . '/' . $this->app->type . 'Controllers/' . $this->moduleName . 'Controller.php';

        if (!empty($this->params[0])) {
            $paths['appUniversalPath_slice'] = $this->app->path . '/modules/' . $this->moduleName . '/Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
            $paths['universalPath_slice'] = $this->path . '/Controllers/' . ucfirst($this->params[0]) . 'Controller.php';
        }
        $paths['appUniversalPath'] = $this->app->path . '/modules/' . $this->moduleName . '/Controllers/' . $this->moduleName . 'Controller.php';
        $paths['universalPath'] = $this->path . '/Controllers/' . $this->moduleName . 'Controller.php';

        return $paths;
    }

    function findController()
    {
        $paths = $this->getControllerPaths();
        foreach ($paths as $pathName => $path) {
            if (file_exists($path)) {
                include $path;
                if (strpos($pathName, 'slice')) {
                    $controllerName = ucfirst($this->params[0]);
                    $params = array_slice($this->params, 1);
                } else {
                    $controllerName = $this->moduleName;
                    $params = $this->params;
                }
                $fullControllerName = $controllerName . 'Controller';
                $controller = new $fullControllerName();
                $controller->params = $params;
                $controller->module = $this;
                $controller->path = pathinfo($path, PATHINFO_DIRNAME);
                $controller->name = $controllerName;
                return $controller;
            }
        }
    }

    static function getInfo($moduleName = '')
    {
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

    function getSnippets($snippetsPath)
    {
        $modulePaths = Module::getModulePaths($this->moduleName);
        $snippets = [];
        foreach ($modulePaths as $path) {
            if (file_exists($path . '/snippets/' . $snippetsPath)) {
                $snippetsPaths = array_slice(scandir($path . '/snippets/' . $snippetsPath), 2);
                foreach ($snippetsPaths as $snippetPath) {
                    if (is_dir($path . '/snippets/' . $snippetsPath . '/' . $snippetPath)) {
                        $snippets[$snippetPath] = include $path . '/snippets/' . $snippetsPath . '/' . $snippetPath . '/info.php';
                    } else {
                        $snippets[$snippetPath] = include $path . '/snippets/' . $snippetsPath . '/' . $snippetPath . '.php';
                    }
                }
            }
        }
        return $snippets;
    }

}
