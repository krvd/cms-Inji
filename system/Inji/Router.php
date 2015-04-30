<?php

/**
 * Router
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Router {

    function init() {
        Inji::app()->listen('UninitializeObjectCalled', 'InjiRouter', ['module' => 'Router', 'method' => 'findModuleClassCatcher']);
        spl_autoload_register([$this, 'findClass']);
    }

    function findModuleClassCatcher($event) {
        return $this->findModuleClass($event['eventObject']);
    }

    function findClass($className) {
        if (strpos($className, '\\')) {
            $classPath = explode('\\', $className);
            $path = Inji::app()->$classPath[0]->path . '/objects/' . $classPath[1] . '.php';
            if (file_exists($path)) {
                include $path;
                return true;
            }
            $path = Inji::app()->$classPath[0]->path . '/models/' . $classPath[1] . '.php';
            if (file_exists($path)) {
                include $path;
                return true;
            }
        }
        return false;
    }

    function findModuleClass($moduleName) {
        if (file_exists(Inji::app()->curApp['path'] . '/modules/' . $moduleName . '/' . $moduleName . '.php')) {
            include Inji::app()->curApp['path'] . '/modules/' . $moduleName . '/' . $moduleName . '.php';
            return true;
        }
        if (file_exists(INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/' . $moduleName . '.php')) {
            include INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/' . $moduleName . '.php';
            return true;
        }
        
        if (!empty(Inji::app()->config->app['moduleRouter'])) {
            foreach (Inji::app()->config->app['moduleRouter'] as $route => $module) {
                if (preg_match("!{$route}!i", $moduleName)) {
                    return $module;
                }
            }
        }
        if (!empty(Inji::app()->config->system['moduleRouter'])) {
            foreach (Inji::app()->config->system['moduleRouter'] as $route => $module) {
                if (preg_match("!{$route}!i", $moduleName)) {
                    return $module;
                }
            }
        }
        return false;
    }

    function uriParse($uri) {
        $answerPos = strpos($uri, '?');
        $params = array_slice(explode('/', substr($uri, 0, $answerPos ? $answerPos : strlen($uri) )), 1);

        foreach ($params as $key => $param) {
            if ($param != '') {
                $params[$key] = urldecode($param);
            } else {
                unset($params[$key]);
            }
        }
        return $params;
    }

    function resolveApp($domain, $uri) {
        $params = $this->uriParse($uri);
        $routes = Inji::app()->Config->custom(INJI_PROGRAM_DIR . '/domainsRoute.php');
        $app = [
            'path' => '',
            'name' => '',
            'type' => 'app',
            'system' => false,
            'params' => $params,
            'static_path' => "/static",
            'templates_path' => "/static/templates",
            'parent' => ''
        ];
        $finalApp = '';
        if (!empty($routes['default_app'])) {
            $finalApp = $routes['default_app'];
        }
        foreach ($routes as $route => $appName) {
            if ($route == 'default_app')
                continue;
            if (preg_match("!{$route}!i", $domain)) {
                $finalApp = $appName;
                break;
            }
        }
        if ($finalApp)
            $app['name'] = $finalApp;
        else
            $app['name'] = $domain;

        $app['path'] = INJI_PROGRAM_DIR . '/' . $app['name'];

        if (!empty($params[0]) && file_exists(INJI_SYSTEM_DIR . '/program/' . $params[0] . '/')) {
            $app['parent'] = $app;
            $app['name'] = $params[0];
            $app['params'] = array_slice($params, 1);

            $app['system'] = true;
            $app['static_path'] = "/{$app['name']}/static";
            $app['templates_path'] = "/{$app['name']}/static/templates";
            $app['path'] = INJI_SYSTEM_DIR . '/program/' . $app['name'];
            $app['type'] = 'app' . ucfirst(strtolower($app['name']));
        }

        return $app;
    }

    function resolveModule($app) {
        $moduleName = false;
        if (!empty($app['params'][0]) && Inji::app()->{$app['params'][0]}) {
            $module = Inji::app()->{$app['params'][0]};
            $module->params = array_slice($app['params'], 1);
            return $module;
        }
        if (!empty(Inji::app()->config->app['defaultModule']) && Inji::app()->{Inji::app()->config->app['defaultModule']}) {
            $module = Inji::app()->{Inji::app()->config->app['defaultModule']};
            $module->params = $app['params'];
            return $module;
        }
    }

    function getLoadedClassPath($className) {
        $paths = get_included_files();
        foreach ($paths as $path) {
            if (preg_match('![/\\\]' . $className . '\.php$!', $path)) {
                return preg_replace('![/\\\]' . $className . '\.php$!', '', $path);
            }
        }
    }

}
