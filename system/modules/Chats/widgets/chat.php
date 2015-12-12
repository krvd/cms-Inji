<?php
if (!empty($params[0])) {
    $chatId = $params[0];
}
if (empty($chatId)) {
    echo 'Чат не найден';
    return;
}
$chat = \Chats\Chat::get($chatId);
if (!$chat) {
    echo 'Чат не найден';
    return;
}
$id = 'chat-' . Tools::randomString();
$msgTemplate = '<div class="chats-chat-message"><small class="text-mutted">message-date_create</small> <b>user-name</b>: message-text</div>';
$events = $chat->events(['order' => ['date_create', 'DESC'], 'key' => false]);
$lastDate = $events ? $events[0]->date_create : 0;
?>

<div id ="<?= $id; ?>" class="chats-chat" data-chat-id="<?= $chatId; ?>" data-last-event-date="<?= $lastDate; ?>" data-msg-count="<?= $chat->messages(['count' => true]); ?>">
  <div class ="chats-chat-message-template" style="display: none;">
    <?= $msgTemplate; ?>
  </div>
  <div class="chats-chat-inputarea"></div>
  <div class="chats-chat-messageList"></div>
</div>
<script>
    inji.onLoad(function () {
      inji.Chats.get('<?= $id; ?>')
    })
</script>