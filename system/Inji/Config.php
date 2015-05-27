<?php

class Config {

    private static $_configs = array();

    /**
     * Load system config
     * 
     * @return array
     */
    public static function system() {
        if (isset(self::$_configs['system']))
            return self::$_configs['system'];

        if (!file_exists(INJI_SYSTEM_DIR . '/config/config.php'))
            return [];

        return self::$_configs['system'] = include INJI_SYSTEM_DIR . '/config/config.php';
    }

    /**
     * Load custom config
     * 
     * @param string $path
     * @return array
     */
    public static function custom($path) {
        if (isset(self::$_configs['custom'][$path]))
            return self::$_configs['custom'][$path];

        if (!file_exists($path))
            return [];

        return self::$_configs['custom'][$path] = include $path;
    }

    /**
     * Load app config
     * 
     * @param type $site_name
     * @return type
     */
    public static function app($app) {
        if (isset(self::$_configs['app'][$app->name]))
            return self::$_configs['app'][$app->name];

        $path = $app->path . "/config/config.php";
        if (!file_exists($path))
            return array();

        return self::$_configs['app'][$app->name] = include $path;
    }

    /**
     * Load share config
     * 
     * @param type $site_name
     * @return type
     */
    public static function share($module = '') {
        if ($module) {
            if (isset($_configs['shareModules'][$module]))
                return self::$_configs['shareModules'][$module];

            $path = INJI_PROGRAM_DIR . "/config/modules/{$module}.php";
        } else {
            if (isset($_configs['share']))
                return self::$_configs['share'];

            $path = INJI_PROGRAM_DIR . "/config/config.php";
        }
        if (!file_exists($path)) {
            
            if (file_exists(INJI_SYSTEM_DIR . "/modules/{$module}/defaultConfig.php")) {
                
                $path = INJI_SYSTEM_DIR . "/modules/{$module}/defaultConfig.php";
            }
            else {
                return array();
            }
        }
        
        if ($module) {
            return self::$_configs['shareModules'][$module] = include $path;
        } else {
            return self::$_configs['share'] = include $path;
        }
    }

    public static function module($module_name, $system = false, $app = null) {
        if (!$app) {
            $app = App::$cur;
        }
        if ($system) {
            $appName = 'system';
            $appPath = INJI_SYSTEM_DIR;
        } else {
            $appName = $app->name;
            $appPath = $app->path;
        }

        if (isset(self::$_configs['module'][$appName][$module_name])) {
            return self::$_configs['module'][$appName][$module_name];
        }

        $path = $appPath . "/config/modules/{$module_name}.php";

        if (!file_exists($path)) {
            $path = INJI_SYSTEM_DIR . "/modules/{$module_name}/defaultConfig.php";
        }


        if (!file_exists($path)) {
            return array();
        }
        return self::$_configs['module'][$appName][$module_name] = include $path;
    }

    public static function save($type, $data, $module = NULL, $app = null) {
        if (!$app) {
            $app = App::$cur;
        }

        $site_name = $app->name;
        $app_name = $app->name;
        switch ($type) {
            case 'system':
                $path = INJI_SYSTEM_DIR . '/config/config.php';
                self::$_configs['system'] = $data;
                break;
            case 'app':
                $path = $app->path . "/config/config.php";
                self::$_configs['site'][$site_name] = $data;
                break;
            case 'module' :
                $path = $app->path . "/config/modules/{$module}.php";
                self::$_configs['module'][$site_name][$module] = $data;
                break;
            case 'share':
                if ($module) {
                    $path = INJI_PROGRAM_DIR . "/config/modules/{$module}.php";
                    self::$_configs['shareModules'][$module] = $data;
                } else {
                    $path = INJI_PROGRAM_DIR . "/config/config.php";
                    self::$_configs['share'] = $data;
                }

                break;
            default:
                $path = $type;
                self::$_configs['custom'][$path] = $data;
                break;
        }
        $text = self::save_parse($data);
        App::$cur->Files->create_dir(substr($path, 0, strripos($path, '/')));
        file_put_contents($path, $text);
    }

    private static function save_parse($data, $level = 0) {
        $return = '';
        if ($level == 0)
            $return = "<?php\nreturn [";
        foreach ($data as $key => $item) {
            $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "'{$key}' => ";
            if (!is_array($item))
                $return .= "'{$item}',";
            else {
                $return .= "[";
                $return .= rtrim(self::save_parse($item, $level + 1), ',');
                $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "],";
            }
        }
        if ($level == 0)
            $return = rtrim($return, ',') . "\n];";

        return $return;
    }

}
