<?php

return function($reward, $user) {
    return [
        'date' => date('Y-m-d H:i:s', mktime(0, 0, 0, date('n') + 1, 1, date("Y"))),
        'type' => 'burn'
    ];
};
