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
            $classPath = array_slice($classPath, 1);

            $paths = [
                'appObject' => App::$cur->path . '/modules/' . $moduleName . '/objects/' . implode('/', $classPath) . '.php',
                'appObjectDir' => App::$cur->path . '/modules/' . $moduleName . '/objects/' . $classPath[0] . '/' . $classPath[0] . '.php',
                'appModel' => App::$cur->path . '/models/' . $classPath[0] . '.php',
                'object' => INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/objects/' . implode('/', $classPath) . '.php',
                'objectDir' => INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/objects/' . $classPath[0] . '/' . $classPath[0] . '.php',
                'model' => INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/models/' . $classPath[0] . '.php',
            ];

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    if (!App::$cur->isLoaded($moduleName)) {
                        App::$cur->loadObject($moduleName);
                    }
                    return true;
                }
            }
        } else {
            $path = INJI_SYSTEM_DIR . '/objects/' . $className . '.php';
            if (file_exists($path)) {
                include_once $path;
                return true;
            }
            $path = INJI_SYSTEM_DIR . '/models/' . $className . '.php';
            if (file_exists($path)) {
                include_once $path;
                return true;
            }
        }
        return false;
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
