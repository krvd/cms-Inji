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
        Inji::app()->listen('UninitializeObjectCalled', 'InjiRouter', ['module' => 'Router', 'method' => 'resolveObjectAlias']);
    }

    function resolveObjectAlias($event) {

        if (file_exists(INJI_SYSTEM_DIR . '/modules/' . $event['eventObject'] . '/' . $event['eventObject'] . '.php')) {
            include INJI_SYSTEM_DIR . '/modules/' . $event['eventObject'] . '/' . $event['eventObject'] . '.php';
            return new $event['eventObject']();
        }
        return $event['eventObject'];
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
            'type' => 'site',
            'system' => false,
            'params' => array(),
            'parent' => ''
        ];
        $finalApp = '';
        if (!empty($routes['default_app'])) {
            $finalApp = $routes['default_app'];
        }
        foreach ($routes as $route => $appName) {
            if ($route == 'default_app')
                continue;
            if (preg_match("!{$route}!", $domain)) {
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
            $app['path'] = INJI_SYSTEM_DIR . '/program/' . $app['name'];
            $app['type'] = 'app_' . mb_strtolower($app['name'], 'utf-8');
        }

        return $app;
    }

    function resolveModule($app) {
        $moduleName = false;
        if (!empty($app['params'][0]) && file_exists($app['path'] . '/modules/' . $app['params'][0])) {

            $moduleName = $app['params'][0];
            include $app['path'] . '/modules/' . $moduleName . '/' . $moduleName . '.php';
            $module = new $moduleName();
            $module->params = array_slice($app['params'], 1);
            $module->path = $app['path'] . '/modules/' . $moduleName;
            return $module;
        }
        if (!empty(Inji::app()->config->app['defaultModule']) && !empty($app['path'] . '/modules/' . Inji::app()->config->app['defaultModule'])) {
            include $app['path'] . '/modules/' . Inji::app()->config->app['defaultModule'] . '/' . Inji::app()->config->app['defaultModule'] . '.php';
            $moduleName = Inji::app()->config->app['defaultModule'];
            $module = new $moduleName();
            $module->params = $app['params'];
            $module->path = $app['path'] . '/modules/' . $moduleName;
            return $module;
        }
    }

}
