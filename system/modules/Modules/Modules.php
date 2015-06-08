<?php

/**
 * Modules class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Modules extends Module {

    function createBlankModule($name, $codeName) {
        Tools::createDir(App::$primary->path . '/modules/' . $codeName);
        ob_start();
        include $this->path . '/moduleTpls/BlankModule.php';
        $moduleCode = ob_get_contents();
        ob_end_clean();
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/' . $codeName . '.php', $moduleCode);
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/info.php', Config::buildPhpArray(['name' => $name]));
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/generatorHash.php', Config::buildPhpArray(['moduleFile' => md5($moduleCode)]));
    }

    function install($module, $params = []) {

        $type = 'modules';

        $path = INJI_SYSTEM_DIR . '/modules/';
        $location = 'modules';

        $config = Config::app(App::$primary ? App::$primary : App::$cur);
        $modules = !empty($config[$location]) ? array_flip($config[$location]) : [];
        if (isset($modules[$module])) {
            return true;
        }
        $info = Module::getInfo($module);

        $config[$location][] = $module;
        if (!empty($info['autoload'])) {
            $config['autoloadModules'][] = $module;
        }
        if (file_exists($path . $module . '/install_script.php')) {
            $installFunction = include $path . $module . '/install_script.php';
            $installFunction(1, $params);
        }
        Config::save('app', $config, null, App::$primary ? App::$primary : App::$cur);
    }

}
