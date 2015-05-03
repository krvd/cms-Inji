<?php

/**
 * Controller class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Controller {

    public $params = [];
    public $path = '';
    public $method = 'index';
    public $module = null;
    public $name = '';

    /**
     * Run controller
     */
    function run() {

        if (!empty($this->params[0]) && method_exists($this, $this->params[0] . 'Action')) {
            $this->method = $this->params[0];
            $this->params = array_slice($this->params, 1);
        } elseif (!method_exists($this, $this->method . 'Action')) {
            INJI_SYSTEM_ERROR('method not found', true);
        }
        if (!$this->checkAccess()) {
            Inji::app()->url->redirect($this->access->getDeniedRedirect(), 'У вас нет прав доступа');
        }
        call_user_func_array([$this, $this->method . 'Action'], $this->params);
    }

    /**
     * Reference to short access core modules
     */
    function __get($name) {
        return Inji::app()->__get($name);
    }

    /**
     * Check access to controller method
     * 
     * @return boolean
     */
    function checkAccess() {
        $accesses = $this->access->config[Inji::app()->curApp['type']];
        $access = array();

        if (isset($accesses['dostup_tree'][$this->module->moduleName][$this->name][$this->method]['_access'])) {
            $access = $accesses['dostup_tree'][$this->module->moduleName][$this->name][$this->method]['_access'];
        } elseif (isset($accesses['dostup_tree'][$this->module->moduleName][$this->name]['_access']))
            $access = $accesses['dostup_tree'][$this->module->moduleName][$this->name]['_access'];
        elseif (isset($accesses['dostup_tree'][$this->module->moduleName]['_access']))
            $access = $accesses['dostup_tree'][$this->module->moduleName]['_access'];
        elseif (isset($accesses['dostup_tree']['_access']))
            $access = $accesses['dostup_tree']['_access'];

        if (Inji::app()->Users->cur->user_group_id && !empty($access) && !in_array(Inji::app()->Users->cur->user_group_id, $access))
            return false;


        return true;
    }

}
