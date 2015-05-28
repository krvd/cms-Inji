<?php

/**
 * App class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class App {

    /**
     * static instances
     */
    static public $cur = null;
    static public $parent = null;
    private $_objects = [];

    /**
     * App params
     */
    public $name = '';
    public $dir = '';
    public $type = 'app';
    public $system = false;
    public $default = false;
    public $route = '';
    public $installed = false;
    public $staticPath = '/static';
    public $templatesPath = '/static/templates';
    public $path = '';
    public $params = [];
    public $config = [];

    /**
     * Constructor App
     * 
     * @param array $preSet
     */
    function __construct($preSet = []) {
        foreach ($preSet as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Return module object by name or alias
     * 
     * @param string $className
     * @return object
     */
    function getObject($className) {
        $className = ucfirst($className);
        if (isset($this->_objects[$className])) {
            return $this->_objects[$className];
        }
        return $this->loadObject($className);
    }

    /**
     * Find module class from each paths
     * 
     * @param string $moduleName
     * @return mixed
     */
    function findModuleClass($moduleName) {
        if (file_exists($this->path . '/modules/' . $moduleName . '/' . $moduleName . '.php')) {
            include_once  $this->path . '/modules/' . $moduleName . '/' . $moduleName . '.php';
            return $moduleName;
        }
        if (file_exists(INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/' . $moduleName . '.php')) {
            include_once INJI_SYSTEM_DIR . '/modules/' . $moduleName . '/' . $moduleName . '.php';
            return $moduleName;
        }
        if (!empty($this->config['moduleRouter'])) {
            foreach ($this->config['moduleRouter'] as $route => $module) {
                if (preg_match("!{$route}!i", $moduleName)) {
                    return $module;
                }
            }
        }
        if (!empty(Inji::$config['moduleRouter'])) {
            foreach (Inji::$config['moduleRouter'] as $route => $module) {
                if (preg_match("!{$route}!i", $moduleName)) {
                    return $module;
                }
            }
        }
        return false;
    }

    /**
     * Load module by name or alias
     * 
     * @param string $className
     * @return mixed
     */
    function loadObject($className) {
        $moduleClassName = $this->findModuleClass($className);
        if (!is_bool($moduleClassName) && $moduleClassName != $className) {
            return $this->_objects[$moduleClassName] = $this->_objects[$className] = $this->getObject($moduleClassName);
        } elseif (class_exists($className)) {
            $this->_objects[$className] = new $className();
        }


        if (isset($this->_objects[$className])) {
            if (method_exists($this->_objects[$className], 'init')) {
                $this->_objects[$className]->init();
            }
            return $this->_objects[$className];
        }
        return null;
    }

    function __get($className) {
        return $this->getObject($className);
    }

}
