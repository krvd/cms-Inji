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

    function install($module, $params = []) {

        $type = 'modules';

        $path = INJI_SYSTEM_DIR . '/modules/';
        $location = 'modules';

        $config = Config::app(App::$parent ? App::$parent : App::$cur);
        $modules = !empty($config[$location]) ? array_flip($config[$location]) : [];
        if (isset($modules[$module])) {
            return true;
        }
        $info = Module::getInfo($module);

        $config[$location][] = $module;
        if (!empty($info['autoload'])) {
            $config['autoloadModules'][] = $module;
        }

        Config::save('app', $config, null, App::$parent ? App::$parent : App::$cur);

        if (file_exists($path . $module . '/default_config.php')) {
            $config = include $path . $module . '/default_config.php';
            $this->Config->save('module', $config, $module);
        }
        if (file_exists($path . $module . '/install_script.php')) {
            $installFunction = include $path . $module . '/install_script.php';
            $installFunction(1, $params);
        }
        $this->modConf = $gconfig;
    }

}
