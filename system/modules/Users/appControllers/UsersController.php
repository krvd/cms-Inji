<?php

class UsersController extends Controller {

    function indexAction() {
        Tools::redirect('/users/profile');
    }

    function profileAction() {
        $this->view->setTitle('Профиль');
        $form = new Ui\ActiveForm(Users\User::$cur->info, 'profile');
        $form->header = false;
        $form->checkRequest();
        $this->view->setTitle('Редактирование профиля');
        $this->view->page(['data' => compact('form')]);
    }

    function loginAction() {
        $this->view->setTitle('Авторизация');
        $this->view->page();
    }

    function registrationAction() {
        $this->view->setTitle('Регистрация');
        if (Users\User::$cur->user_id) {
            Tools::redirect('/', 'Вы уже зарегистрированы');
        }
        if (!empty($_POST)) {
            $error = false;
            $response = $this->Recaptcha->check($_POST['g-recaptcha-response']);
            if ($response) {
                if (!$response->success) {
                    Msg::add('Вы не прошли проверку на робота', 'danger');
                    $error = true;
                }
            } else {
                Msg::add('Произошла ошибка, попробуйте ещё раз');
                $error = true;
            }
            if (!$error) {
                $user_id = $this->Users->registration($_POST);
                if ($user_id) {
                    Tools::redirect('/');
                }
            }
        }
        $this->view->setTitle('Регистрация');
        $this->view->page();
    }
    function activationAction($userId = 0, $hash = '') {
        $user = \Users\User::get((int) $userId);
        if (!$user || $user->activation !== (string) $hash) {
            Tools::redirect('/', 'Во время активации произошли ошибки', 'danger');
        }
        $user->activation = '';
        $user->save();
        Tools::redirect('/', 'Вы успешно активировали ваш аккаунт, теперь вы можете войти');
    }

    function logoutAction() {
        setcookie("user_login", '', 0, "/");
        setcookie("user_mail", '', 0, "/");
        setcookie("user_pass", '', 0, "/");
        $accesses = $this->Config->module('Access');
        Tools::redirect($accesses['site']['denied_redirect'], 'Вы вышли из профиля');
    }

}

?>
