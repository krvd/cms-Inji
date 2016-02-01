<?php

/**
 * Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Controller
{
    /**
     * Storage of cur requested controller
     * 
     * @var Controller 
     */
    public static $cur = null;

    /**
     * Requested params for method
     * 
     * @var array 
     */
    public $params = [];

    /**
     * Path to controller dir
     * 
     * @var string 
     */
    public $path = '';

    /**
     * Requested action name
     * 
     * @var string 
     */
    public $method = 'index';

    /**
     * Module of this controller
     * 
     * @var Module 
     */
    public $module = null;

    /**
     * This controller name
     * 
     * @var string 
     */
    public $name = '';

    /**
     * Flag of controller runing
     * 
     * @var boolean 
     */
    public $run = false;

    /**
     * Run controller
     */
    function run()
    {
        if (!empty($this->params[0]) && method_exists($this, $this->params[0] . 'Action')) {
            $this->method = $this->params[0];
            $this->params = array_slice($this->params, 1);
        } elseif (!method_exists($this, $this->method . 'Action')) {
            INJI_SYSTEM_ERROR('method not found', true);
        }
        if (!$this->checkAccess()) {
            Tools::redirect($this->access->getDeniedRedirect(), 'У вас нет прав доступа');
        }
        $this->run = true;
        call_user_func_array([$this, $this->method . 'Action'], $this->params);
    }

    /**
     * Reference to short access core modules
     */
    function __get($name)
    {
        return App::$cur->__get($name);
    }

    /**
     * Reference to short access core modules
     */
    function __call($name, $params)
    {
        return App::$cur->__call($name, $params);
    }

    /**
     * Check access to controller method
     * 
     * @return boolean
     */
    function checkAccess()
    {
        if ($this->module->app->access) {
            return $this->module->app->access->checkAccess($this);
        }
        return true;
    }

}
