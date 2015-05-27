<?php

class Url extends Module {

    function redirect($href = '/', $text = false, $status = 'info') {

        if ($text !== false)
            App::$cur->msg->add($text, $status);

        header("Location: {$href}");
        exit("Перенаправление на: <a href = '{$href}'>{$href}</a>");
    }

}
