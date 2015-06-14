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

    static $cur = null;
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
            Tools::redirect($this->access->getDeniedRedirect(), 'У вас нет прав доступа');
        }
        call_user_func_array([$this, $this->method . 'Action'], $this->params);
    }

    /**
     * Reference to short access core modules
     */
    function __get($name) {
        return App::$cur->__get($name);
    }

    /**
     * Check access to controller method
     * 
     * @return boolean
     */
    function checkAccess() {
        return $this->module->app->access->checkAccess($this, Users\User::$cur);
    }

}
