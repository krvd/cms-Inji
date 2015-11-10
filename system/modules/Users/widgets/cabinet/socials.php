<?php

$socials = Users\Social::getList(['where' => ['active', 1]]);
foreach ($socials as $social) {
    $connect = false;
    foreach (Users\User::$cur->socials as $userSocial) {
        if ($userSocial->social_id == $social->id) {
            $connect = true;
            break;
        }
    }
    if ($connect) {
        echo "<a href = '/users/social/disconnect/{$social->code}'>Отключить {$social->name}</a><br />";
    } else {
        echo "<a href = '/users/social/auth/{$social->code}'>Подключить {$social->name}</a><br />";
    }
}