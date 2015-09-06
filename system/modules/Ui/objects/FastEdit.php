<?php

/**
 * Fast edit object
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui;

class FastEdit extends \Object {

    static function block($object, $col, $value = null, $parse = false) {
        echo "<div class = 'fastEdit' data-model='" . get_class($object) . "' data-col='{$col}' data-key='" . $object->pk() . "'>";
        $value = $value !== null ? $value : $object->$col;
        if($parse){
            \App::$cur->view->parseSource($value);
        }
        echo "</div>";
    }

}
