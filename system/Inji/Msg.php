<?php

class Msg extends Module {

    static function add($text = false, $status = 'info') {
        if ($text !== false)
            $_SESSION['_INJI_MSG'][] = array('text' => $text, 'status' => $status);
    }

    static function show() {
        App::$cur->view->widget('msgList');
    }

    static function get($clean = true) {
        if (empty($_SESSION['_INJI_MSG']))
            return [];

        return $_SESSION['_INJI_MSG'];
    }

    static function flush() {
        $_SESSION['_INJI_MSG'] = [];
    }

}
