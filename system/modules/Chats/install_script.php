<?php

return function ($step = NULL, $params = []) {
    $statuses = [
        [
            'name' => 'Онлайн',
        ], [
            'name' => 'Отошел',
        ], [
            'name' => 'Заблокирован',
        ]
    ];
    foreach ($statuses as $status) {
        $status = new Chats\Chat\Member\Status($status);
        $status->save();
    }
};
