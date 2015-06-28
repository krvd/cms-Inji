<?php

class UsersController extends Controller {

    function indexAction() {
        $this->view->setTitle('Пользователи');
        $dataManager = new Ui\DataManager('Users\User');
        $this->view->page(['data' => compact('dataManager')]);
    }

    function loginAction() {
        if (!Users\User::$cur->user_id) {
            $this->view->page(['template' => 'login', 'content' => 'login']);
        } else {
            $this->view->page(['content' => 'profile']);
        }
    }

    function loginAsAction($userId) {
        $user = Users\User::get($userId);
        $this->users->autorization($user->user_mail, $user->user_pass, 'mail');
        Tools::redirect('/', 'Теперь вы на сайте под пользователем ' . $user->user_mail);
    }

}
