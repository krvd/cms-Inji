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
        $codeName = ucfirst($codeName);
        Tools::createDir(App::$primary->path . '/modules/' . $codeName);
        ob_start();
        include $this->path . '/tpls/BlankModule.php';
        $moduleCode = ob_get_contents();
        ob_end_clean();
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/' . $codeName . '.php', $moduleCode);
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/info.php', Config::buildPhpArray(['name' => $name]));
        file_put_contents(App::$primary->path . '/modules/' . $codeName . '/generatorHash.php', Config::buildPhpArray([$codeName . '.php' => md5($moduleCode)]));
    }

    function createModel($module, $name, $codeName, $options) {
        $codeName = ucfirst($codeName);
        $cols = [];
        if (!empty($options['cols'])) {
            $colPrefix = strtolower($codeName) . '_';
            $tableCols = [$colPrefix . 'id' => 'pk'];
            foreach ($options['cols'] as $col) {
                $cols[$col['code']] = ['type' => $col['type']];
                $labels[$col['code']] = $col['label'];
                switch ($col['type']) {
                    case 'image':
                    case 'number':
                        $tableCols[$colPrefix . $col['code']] = 'int(11) NOT NULL';
                        break;
                    case 'decimal':
                        $tableCols[$colPrefix . $col['code']] = 'decimal(11,2) NOT NULL';
                        break;
                    case 'dateTime':
                        $tableCols[$colPrefix . $col['code']] = 'timestamp NOT NULL';
                        break;
                    case 'currentDateTime':
                        $tableCols[$colPrefix . $col['code']] = 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP';
                        break;
                    case 'text':
                        $tableCols[$colPrefix . $col['code']] = 'varchar(255) NOT NULL';
                        break;
                    case 'textarea':
                    default:
                        $tableCols[$colPrefix . $col['code']] = 'text NOT NULL';
                        break;
                }
            }
            App::$cur->db->createTable(strtolower($module) . '_' . strtolower($codeName), $tableCols);
        }

        Tools::createDir(App::$primary->path . '/modules/' . $module . '/models');
        ob_start();
        include $this->path . '/tpls/Model.php';
        $modelCode = ob_get_contents();
        ob_end_clean();
        file_put_contents(App::$primary->path . '/modules/' . $module . '/models/' . $codeName . '.php', $modelCode);
        $config = Config::custom(App::$primary->path . '/modules/' . $module . '/generatorHash.php');
        $config['models/' . $codeName . '.php'] = md5($modelCode);
        Config::save(App::$primary->path . '/modules/' . $module . '/generatorHash.php', $config);
    }

    function editModel($module, $name, $codeName, $options) {
        $codeName = ucfirst($codeName);
        $cols = [];
        if (!empty($options['cols'])) {
            $colPrefix = strtolower($codeName) . '_';
            $tableCols = [];
            $tableName = strtolower($module) . '_' . strtolower($codeName);

            $colsExist = App::$cur->db->getTableCols($tableName);
            if (empty($colsExist[$colPrefix . 'id'])) {
                $tableCols[$colPrefix . 'id'] = 'pk';
            }

            foreach ($options['cols'] as $col) {
                $colType = !empty($col['type']['primary']) ? $col['type']['primary'] : $col['type'];
                $cols[$col['code']] = ['type' => $colType];
                if ($colType == 'relationParent') {
                    $cols[$col['code']]['relation'] = $col['type']['aditional'];
                }
                $labels[$col['code']] = $col['label'];
                if (!empty($colsExist[$colPrefix . $col['code']])) {
                    continue;
                }

                switch ($colType) {
                    case 'image':
                    case 'number':
                    case 'relationParent':
                        $tableCols[$colPrefix . $col['code']] = 'int(11) NOT NULL';
                        break;
                    case 'decimal':
                        $tableCols[$colPrefix . $col['code']] = 'decimal(11,2) NOT NULL';
                        break;
                    case 'dateTime':
                        $tableCols[$colPrefix . $col['code']] = 'timestamp NOT NULL';
                        break;
                    case 'currentDateTime':
                        $tableCols[$colPrefix . $col['code']] = 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP';
                        break;
                    case 'text':
                        $tableCols[$colPrefix . $col['code']] = 'varchar(255) NOT NULL';
                        break;
                    case 'textarea':
                    default:
                        $tableCols[$colPrefix . $col['code']] = 'text NOT NULL';
                        break;
                }
            }
            foreach ($tableCols as $colKey => $params) {
                App::$cur->db->add_col($tableName, $colKey, $params);
            }
        }

        Tools::createDir(App::$primary->path . '/modules/' . $module . '/models');
        ob_start();
        include $this->path . '/tpls/Model.php';
        $modelCode = ob_get_contents();
        ob_end_clean();
        file_put_contents(App::$primary->path . '/modules/' . $module . '/models/' . $codeName . '.php', $modelCode);
        $config = Config::custom(App::$primary->path . '/modules/' . $module . '/generatorHash.php');
        $config['models/' . $codeName . '.php'] = md5($modelCode);
        Config::save(App::$primary->path . '/modules/' . $module . '/generatorHash.php', $config);
    }

    function install($module, $params = []) {

        $type = 'modules';

        $path = INJI_SYSTEM_DIR . '/modules/';
        $location = 'modules';

        $config = Config::app();
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

    function getSelectListModels($module = false) {
        $models = [];
        if ($module) {
            $modelsNames = $this->getModelsList($module);

            $info = Modules::getInfo($module);
            $moduleName = !empty($info['name']) ? $info['name'] : $module;
            foreach ($modelsNames as $modelName) {
                $fullModelName = $module . '\\' . $modelName;
                $models[$fullModelName] = $moduleName . ' - ' . ($fullModelName::$objectName ? $fullModelName::$objectName : $modelName);
            }
        }
        foreach (App::$primary->config['modules'] as $configModule) {
            if ($module == $configModule) {
                continue;
            }
            $modelsNames = $this->getModelsList($configModule);

            $info = Modules::getInfo($configModule);
            $moduleName = !empty($info['name']) ? $info['name'] : $configModule;
            foreach ($modelsNames as $modelName) {
                $fullModelName = $configModule . '\\' . $modelName;
                Router::loadClass($fullModelName);
                $models[$fullModelName] = $moduleName . ' - ' . ($fullModelName::$objectName ? $fullModelName::$objectName : $modelName);
            }
        }
        return $models;
    }

    function getModelsList($module) {
        $path = Module::getModulePath($module) . '/models';
        $models = [];
        if (file_exists($path)) {
            foreach (array_slice(scandir($path), 2) as $file) {
                if (is_dir($file)) {
                    continue;
                }
                $models[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        return $models;
    }

}
