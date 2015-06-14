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

namespace Server;

class Result extends \Object {

    public $content = null;
    public $success = true;

    function send() {
        $return = [];
        $return['success'] = $this->success;
        if ($this->success) {
            $return['content'] = $this->content;
        } else {
            $return['error'] = $this->content;
        }
        if (!headers_sent()) {
            header('Content-type: application/json');
        }
        echo json_encode($return);
        exit();
    }

}
