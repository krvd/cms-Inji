<?php

class Config {

    private $_configs = array();

    /**
     * Load system config
     * 
     * @return type
     */
    function system() {
        if (isset($this->_configs['system']))
            return $this->_configs['system'];

        if (!file_exists(INJI_SYSTEM_DIR . '/config/config.php'))
            return array();

        return $this->_configs['system'] = include INJI_SYSTEM_DIR . '/config/config.php';
    }

    /**
     * Load custom config
     * 
     * @param string $path
     * @return array
     */
    function custom($path) {
        if (isset($this->_configs['custom'][$path]))
            return $this->_configs['custom'][$path];

        if (!file_exists($path))
            return array();

        return $this->_configs['custom'][$path] = include $path;
    }

    /**
     * Load app config
     * 
     * @param type $site_name
     * @return type
     */
    function app($parent = false) {
        if ($parent) {
            $app = Inji::app()->curApp['parent'];
        } else {
            $app = Inji::app()->curApp;
        }
        if (isset($this->_configs['app'][$app['name']]))
            return $this->_configs['app'][$app['name']];

        $path = $app['path'] . "/config/config.php";
        if (!file_exists($path))
            return array();

        return $this->_configs['app'][$app['name']] = include $path;
    }

    function module($module_name) {
        if (Inji::app()->curApp['system']) {
            $app = Inji::app()->curApp['parent'];
        } else {
            $app = Inji::app()->curApp;
        }

        if (isset($this->_configs['module'][$app['name']][$module_name])) {
            return $this->_configs['module'][$app['name']][$module_name];
        }

        $path = $app['path'] . "/config/modules/{$module_name}.php";

        if (!file_exists($path)) {
            $path = INJI_SYSTEM_DIR . "/modules/{$module_name}/default_config.php";
        }


        if (!file_exists($path)) {
            return array();
        }
        return $this->_configs['module'][$app['name']][$module_name] = include $path;
    }

    function save($type, $data, $module = NULL) {
        if (!Inji::app()->curApp['system']) {
            $site_name = Inji::app()->curApp['name'];
        } elseif (Inji::app()->curApp['system']) {
            $site_name = Inji::app()->curApp['parent']['name'];
        }

        $app_name = Inji::app()->curApp['name'];
        switch ($type) {
            case 'system':
                $path = INJI_SYSTEM_DIR . '/config/config.php';
                $this->_configs['system'] = $data;
                break;
            case 'site':
                $path = INJI_PROGRAM_DIR . "/{$site_name}/config/config.php";
                $this->_configs['site'][$site_name] = $data;
                break;
            case 'module' :
                $path = INJI_PROGRAM_DIR . "/{$site_name}/config/modules/{$module}.php";
                $this->_configs['module'][$site_name][$module] = $data;
                break;
            default:
                $path = $type;
                $this->_configs['custom'][$path] = $data;
                break;
        }
        $text = $this->save_parse($data);
        Inji::app()->_FS->create_dir(substr($path, 0, strripos($path, '/')));
        file_put_contents($path, $text);
    }

    private function save_parse($data, $level = 0) {
        $return = '';
        if ($level == 0)
            $return = "<?php\nreturn [";
        foreach ($data as $key => $item) {
            $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "'{$key}' => ";
            if (!is_array($item))
                $return .= "'{$item}',";
            else {
                $return .= "[";
                $return .= rtrim($this->save_parse($item, $level + 1), ',');
                $return .= "\n" . str_repeat(' ', ( $level * 4 + 4)) . "],";
            }
        }
        if ($level == 0)
            $return = rtrim($return, ',') . "\n];";

        return $return;
    }

    function &__get($name) {
        $config = $this->$name();
        return $config;
    }

}
