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
    public function eventsAction($chatId = 0)
    {
        $chatId = (int) $chatId;
        $result = new Server\Result();
        if (!$chatId || !($chat = \Chats\Chat::get($chatId))) {
            $result->success = false;
            $result->content = 'Такого чата не существует';
            $result->send();
        }
        $where = [['chat_id', $chatId]];
        if (!empty($_GET['lastEventDate'])) {
            $where[] = ['date_create', $_GET['lastEventDate'], '>'];
        }
        $result->content = [
            'members' => [],
            'messages' => []
        ];
        if (Users\User::$cur->id) {
            $member = \Chats\Chat\Member::get([['chat_id', $chatId], ['user_id', Users\User::$cur->id]]);
            if (!$member) {
                $member = new \Chats\Chat\Member();
                $member->user_id = Users\User::$cur->id;
                $member->chat_id = $chatId;
            }
            $member->date_last_active = date('Y-m-d H:i:s');
            $member->save();
        }
        $members = $this->module->getMembers($chatId);
        foreach ($members as $member) {
            $result->content['members'][$member->user_id] = [
                'fullUserName' => $member->user->name(),
                'userFirstName' => $member->user->info->first_name,
                'userPhoto' => $member->user->info->photo ? $member->user->info->photo->path : '/static/system/images/no-image.png'
            ];
        }

        $messages = \Chats\Chat\Message::getList(['where' => $where, 'limit' => 20, 'order' => ['date_create', 'DESC']]);
        $messages = array_reverse($messages);
        foreach ($messages as $message) {
            $msg = [
                'message' => $message->_params,
                'fullUserName' => $message->user->name(),
                'userFirstName' => $message->user->info->first_name,
                'userPhoto' => $message->user->info->photo ? $message->user->info->photo->path : '/static/system/images/no-image.png'
            ];
            $result->content['messages'][] = $msg;
        }
        $result->send();
    }

    public function sendFormAction($chatId = 0)
    {
        $chatId = (int) $chatId;
        $result = new Server\Result();
        if (!$chatId || !($chat = \Chats\Chat::get($chatId))) {
            $result->success = false;
            $result->content = 'Такого чата не существует';
            $result->send();
        }
        if (!Users\User::$cur->id) {
            $result->success = false;
            $result->content = 'Вы не авторизованы';
            $result->send();
        }
        if (empty($_POST['chat-message']) || !trim($_POST['chat-message'])) {
            $result->success = false;
            $result->content = 'Сообщение не может быть пустым';
            $result->send();
        }
        $message = new \Chats\Chat\Message();
        $message->user_id = Users\User::$cur->id;
        $message->chat_id = $chatId;
        $message->text = htmlspecialchars(trim($_POST['chat-message']));
        $message->save();
        $result->successMsg = 'Ваше сообщение было отправлено';
        $result->send();
    }

}
