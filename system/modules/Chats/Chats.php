<?php

/**
 * Chats
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Chats extends Module
{
    function init()
    {
        App::$primary->view->customAsset('js', '/moduleAsset/Chats/js/chat.js');
    }

}
