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

namespace Migrations;

class Reader extends \Object {

    public $data = NULL;
    public $source = '';

    function loadData($source = '') {
        $this->source = $source;
        return FALSE;
    }

    function readPath($path = '/') {
        return [];
    }

    function __toString() {
        return '';
    }

}
