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

    static function findClass($className) {
        if (strpos($className, '\\')) {
            $classPath = explode('\\', $className);
            $moduleName = $classPath[0];
            $result = Router::loadClass($className);
            if ($result) {
                if (!App::$cur->isLoaded($moduleName)) {
                    App::$cur->loadObject($moduleName);
                }
            }
            return $result;
        } else {
            return Router::loadClass($className);
        }
        return false;
    }

    static function loadClass($className) {
        $paths = [];
        if (strpos($className, '\\')) {
            $classPath = explode('\\', $className);
            $moduleName = $classPath[0];
            $classPath = implode('/', array_slice($classPath, 1));

            if (App::$cur !== App::$primary) {
                $paths['primaryAppModuleObject'] = App::$primary->path . '/modules/' . $moduleName . '/objects/' . $classPath . '.php';
                $paths['primaryAppModuleObjectDir'] = App::$primary->path . '/modules/' . $moduleName . '/objects/' . $classPath . '/' . $classPath . '.php';
                $paths['primaryAppModuleModel'] = App::$primary->path . '/modules/' . $moduleName . '/models/' . $classPath . '.php';
            }
            $paths['appModuleObject'] = App::$cur->path . '/modules/' . $moduleName . '/objects/' . $classPath . '.php';
            $paths['appModuleObjectDir'] = App::$cur->path . '/modules/' . $moduleName . '/objects/' . $classPath . '/' . $classPath . '.php';
            $paths['appModuleModel'] = App::$cur->path . '/modules/' . $moduleName . '/models/' . $classPath . '.php';
            $paths['systemModuleObject'] = INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/objects/' . $classPath . '.php';
            $paths['systemModuleModel'] = INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/models/' . $classPath . '.php';
            $paths['systemModuleObjectDir'] = INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/objects/' . $classPath . '/' . $classPath . '.php';
        }
        $classPath = str_replace('\\', '/', $className);

        if (App::$cur !== App::$primary) {
            $paths['primaryAppObject'] = App::$primary->path . '/objects/' . $classPath . '.php';
            $paths['primaryAppObjectDir'] = App::$primary->path . '/objects/' . $classPath . '/' . $classPath . '.php';
            $paths['primaryAppModel'] = App::$primary->path . '/models/' . $classPath . '.php';
        }
        $paths['appObject'] = App::$cur->path . '/objects/' . $classPath . '.php';
        $paths['appObjectDir'] = App::$cur->path . '/objects/' . $classPath . '/' . $classPath . '.php';
        $paths['appModel'] = App::$cur->path . '/models/' . $classPath . '.php';

        $paths['systemObject'] = INJI_SYSTEM_DIR . '/objects/' . $classPath . '.php';
        $paths['systemObjectDir'] = INJI_SYSTEM_DIR . '/objects/' . $className . '/' . $className . '.php';
        $paths['systemModel'] = INJI_SYSTEM_DIR . '/models/' . $classPath . '.php';

        foreach ($paths as $path) {
            if (file_exists($path)) {
                include_once $path;
                return true;
            }
        }
        return FALSE;
    }

    static function getLoadedClassPath($className) {
        $paths = get_included_files();
        foreach ($paths as $path) {
            if (preg_match('![/\\\]' . $className . '\.php$!', $path)) {
                return preg_replace('![/\\\]' . $className . '\.php$!', '', $path);
            }
        }
    }

}
