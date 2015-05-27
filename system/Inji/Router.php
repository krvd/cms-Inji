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
            $paths = [
                'object' => App::$cur->getObject($classPath[0])->path . '/objects/' . $classPath[1] . '.php',
                'object2' => App::$cur->getObject($classPath[0])->path . '/objects/' . $classPath[1] . '/' . $classPath[1] . '.php',
                'model' => $path = App::$cur->getObject($classPath[0])->path . '/models/' . $classPath[1] . '.php'
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include $path;
                    return true;
                }
            }
        } else {
            $path = INJI_SYSTEM_DIR . '/objects/' . $className . '.php';
            if (file_exists($path)) {
                include $path;
                return true;
            }
            $path = INJI_SYSTEM_DIR . '/models/' . $className . '.php';
            if (file_exists($path)) {
                include $path;
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
