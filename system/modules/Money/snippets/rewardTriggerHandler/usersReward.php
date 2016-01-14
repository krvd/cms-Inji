<?php

return [
    'name' => 'Вознаграждение пользователю за различные действия',
    'handler' => function($user, $trigger) {
        App::$cur->money->reward($trigger->reward_id, [], $user);
    }
        ];
        