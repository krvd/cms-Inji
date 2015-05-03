<?php

class Url extends Module {

    function redirect($href = '/', $text = false, $status = 'info') {

        if ($text !== false)
            Inji::app()->msg->add($text, $status);

        header("Location: {$href}");
        exit("Перенаправление на: <a href = '{$href}'>{$href}</a>");
    }

}
