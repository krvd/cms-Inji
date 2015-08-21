<?php

/**
 * Exchange module for 1c
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Exchange1c extends Module {

    function response($code, $text = '', $exit = true) {
        echo $code;
        if ($text) {
            echo "\n";
            echo $text;
        }
        if ($exit) {
            exit();
        }
    }

}