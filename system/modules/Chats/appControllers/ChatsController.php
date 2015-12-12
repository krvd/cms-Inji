<?php

/**
 * Chat Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class ChatsController extends Controller
{
    function eventsAction($chatId = 0)
    {
        $chatId = (int) $chatId;
        $result = new Server\Result();
        if(!$chatId|| !($chat = \Chats\Chat::get($chatId))){
            $result->success=false;
            $result->content = 'Такого чата не существует';
            $result->send();
        }
        if(!empty($_GET['date']))
        $result->send();
    }

}
