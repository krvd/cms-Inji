<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

/**
 * Description of Controller
 *
 * @author inji
 */
class Controller {

    public $params = [];
    public $path = '';
    public $method = 'index';

    function run() {
        if (!empty($this->params[0]) && method_exists($this, $this->params[0] . 'Action')) {
            $this->method = $this->params[0];
            $this->params = array_slice($this->params, 1);
            call_user_func_array([$this, $this->method . 'Action'], $this->params);
        }
        elseif (method_exists($this, $this->method . 'Action')) {
            call_user_func_array([$this, $this->method . 'Action'], $this->params);
        }
    }

}
