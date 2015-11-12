<?php

class UsersController extends adminController
{
    function loginAction()
    {
        if (!Users\User::$cur->user_id) {
            $this->view->page(['page' => 'login', 'content' => 'login']);
        } else {
            $this->view->page(['content' => 'profile']);
        }
    }

    function loginAsAction($userId)
    {
        $user = Users\User::get($userId);
        App::$cur->users->newSession($user);
        Tools::redirect('/', 'Теперь вы на сайте под пользователем ' . $user->name());
    }

}
