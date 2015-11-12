<?php

/**
 * Router
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Router
{
    static function findClass($className)
    {
        if (strpos($className, '\\')) {
            $classPath = explode('\\', $className);
            $moduleName = $classPath[0];
            $result = Router::loadClass($className);
            if ($result && App::$cur) {
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

    static function loadClass($className)
    {
        $folders = [];
        if (strpos($className, '\\')) {
            $classPath = explode('\\', $className);
            $moduleName = $classPath[0];
            if (Module::installed($moduleName, App::$cur)) {
                $classPath = implode('/', array_slice($classPath, 1));
                if (App::$cur) {
                    if (App::$cur !== App::$primary) {
                        $folders['appModule'] = ['folder' => App::$primary->path . '/modules/' . $moduleName, 'classPath' => $classPath];
                    }
                    $folders['primaryAppModule'] = ['folder' => App::$cur->path . '/modules/' . $moduleName, 'classPath' => $classPath];
                }
                $folders['systemModule'] = ['folder' => INJI_SYSTEM_DIR . '/modules/' . $moduleName, 'classPath' => $classPath];
            }
        }
        $classPath = str_replace('\\', '/', $className);

        if (App::$cur) {
            if (App::$cur !== App::$primary) {
                $folders['primaryApp'] = ['folder' => App::$primary->path, 'classPath' => $classPath];
            }
            $folders['app'] = ['folder' => App::$cur->path, 'classPath' => $classPath];
        }
        $folders['system'] = ['folder' => INJI_SYSTEM_DIR, 'classPath' => $classPath];
        $paths = [];
        foreach ($folders as $code => $folderParams) {
            $paths = $paths + static::genFolderPaths($code, $folderParams['folder'], $folderParams['classPath']);
        }
        foreach ($paths as $path) {
            if (file_exists($path)) {
                include_once $path;
                return true;
            }
        }
        return FALSE;
    }

    static function genFolderPaths($code, $folder, $classPath)
    {
        $paths = [];
        if (strpos($classPath, '/') === false) {
            $paths[$code . '_Object'] = $folder . '/objects/' . $classPath . '.php';
            $paths[$code . '_ObjectDir'] = $folder . '/objects/' . $classPath . '/' . $classPath . '.php';
            $paths[$code . '_Model'] = $folder . '/models/' . $classPath . '.php';
            $paths[$code . '_ModelDir'] = $folder . '/models/' . $classPath . '/' . $classPath . '.php';
        } else {
            $classFile = substr($classPath, strrpos($classPath, '/') + 1);
            $classPathWithotClass = substr($classPath, 0, strrpos($classPath, '/'));

            $paths[$code . '_Object'] = $folder . '/objects/' . $classPathWithotClass . '/' . $classFile . '.php';
            $paths[$code . '_ObjectDir'] = $folder . '/objects/' . $classPath . '/' . $classFile . '.php';
            $paths[$code . '_Model'] = $folder . '/models/' . $classPathWithotClass . '/' . $classFile . '.php';
            $paths[$code . '_ModelDir'] = $folder . '/models/' . $classPath . '/' . $classFile . '.php';
        }
        return $paths;
    }

    static function getLoadedClassPath($className)
    {
        $rc = new ReflectionClass($className);
        return dirname($rc->getFileName());
    }

}
