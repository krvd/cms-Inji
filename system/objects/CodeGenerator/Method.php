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

namespace CodeGenerator;

class Method extends \Object {

    public $security = 'public';
    public $static = false;
    public $name = 'property';
    public $params = [];
    public $body = '';

    function generate() {
        $code = $this->security . ' ';
        $code .= $this->static ? 'static ' : '';
        $code .= 'function ' . $this->name . '(';
        foreach ($this->params as $param) {
            $code .= '$' . $param . ',';
        }
        $code = rtrim($code, ',');
        $code.= ") {\n";
        $code.= '    ' . str_replace("\n", "\n    ", $this->body);
        $code .="\n}";
        return $code;
    }

}
