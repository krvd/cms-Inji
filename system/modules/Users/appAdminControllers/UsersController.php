<?php

class UsersController extends Controller {

    function indexAction() {
        $this->view->set_title('Пользователи');
        $dataTable = new DataTable('User', $_GET, ['actions' => [ 'edit', 'del', ['text' => 'Войти как', 'href' => App::$cur->url->module() . '/loginAs/']]]);
        $this->view->page(compact('dataTable'));
    }

    function createAction() {
        $this->view->set_title('Создание пользователя');
        $roles = $this->roles->get_all();
        $user = newUsers\User();
        if (!empty($_POST)) {
            $error = false;

            if ($_POST['user_pass'][0] == $_POST['user_pass'][1] AND $_POST['user_pass'][0] != '') {
                $_POST['user_pass'] = sha1($_POST['user_pass'][0]);
                $user->user_pass = $this->users->hashpass($_POST['user_pass'][0]);
            } else {
                $this->msg->add('Пароль не указан или указан не верно.', 'danger');
                $error = true;
            }

            if (!$_POST['user_mail']) {
                $this->msg->add('E-mail не указан.', 'danger');
                $error = true;
            } else {
                $user->user_mail = $_POST['user_mail'];
            }

            if (!$_POST['user_login']) {
                $this->msg->add('Логин не указан.', 'danger');
                $error = true;
            } else {
                $user->user_login = $_POST['user_login'];
            }

            $user->user_group_id = $roles[$_POST['user_role_id']]['role_group_id'];
            $user->user_role_id = $_POST['user_role_id'];
            $user->user_name = $_POST['user_name'];



            if (!$error) {
                $user->save();
                $this->msg->add('Пользователь создан.', 'success');
                $this->url->redirect('/admin/Users');
            }
        }
        $this->view->page(compact('roles', 'user'));
    }

    function editAction($user_id) {
        $this->view->set_title('Редактирование пользователя');
        $roles = $this->roles->get_all();
        $user = Users\User::get($user_id);

        if (!empty($_POST)) {
            if (!empty($_POST['user_pass'][0]) && $_POST['user_pass'][0] == $_POST['user_pass'][1])
                $user->user_pass = $this->users->hashpass($_POST['user_pass'][0]);
            else
                unset($_POST['user_pass']);

            $user->user_group_id = $roles[$_POST['user_role_id']]['role_group_id'];
            $user->user_role_id = $_POST['user_role_id'];
            $user->user_name = $_POST['user_name'];
            $user->user_admin_text = $_POST['user_admin_text'];
            $user->user_mail = $_POST['user_mail'];
            $user->save();
            $this->msg->add('Пользователь изменен.', 'success');
            $this->url->redirect('/admin/Users/');
        }
        $user = Users\User::get($user_id);
        $this->view->page(compact('user', 'roles'));
    }

    function delAction($user_id) {
        if ($this->users->delete_user($user_id))
            $this->msg->add('Пользователь удален.', 'success');
        else
            $this->msg->add('Пользователь не удален.', 'danger');

        $this->url->redirect($this->url->up_to(2));
    }

    function logout() {
        setcookie("user_login", '', 0, "/");
        setcookie("user_mail", '', 0, "/");
        setcookie("user_pass", '', 0, "/");
        $accesses = $this->Config->module('Access');
        $this->url->redirect($accesses['denied_redirect'], 'Вы вышли из профиля');
    }

    function loginAction() {
        if (!App::$cur->Users->curUser->user_id) {
            $this->view->page(['template' => 'login', 'content' => 'login']);
        } else {
            $this->view->page(['content' => 'profile']);
        }
    }

    function loginAsAction($userId) {
        $user = Users\User::get($userId);
        $this->users->autorization($user->user_mail, $user->user_pass, 'mail');
        $this->url->redirect('/', 'Теперь вы на сайте под пользователем ' . $user->user_mail);
    }

}
