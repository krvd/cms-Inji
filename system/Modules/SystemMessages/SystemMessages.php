<?php

class SystemMessages extends Module {

    function add($text = false, $status = 'info') {
        if ($text !== false)
            $_SESSION['_INJI_MSG'][] = array('text' => $text, 'status' => $status);
    }

    function show() {
        Inji::app()->view->widget('SystemMessages\msgList');
    }

    function get($clean = true) {
        if (empty($_SESSION['_INJI_MSG']))
            return array();
        else {
            $msgs = $_SESSION['_INJI_MSG'];
            if ($clean)
                unset($_SESSION['_INJI_MSG']);
            return $msgs;
        }
    }

}
