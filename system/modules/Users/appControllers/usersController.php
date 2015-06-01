<?php

class usersController extends Controller
{

    function indexAction()
    {
        Tools::redirect('/users/profile');
    }

    function profileAction()
    {
        $this->view->set_title('Профиль');
        if (!empty($_POST)) {
            $changepass = false;
            $this->users->cur->user_name = htmlspecialchars($_POST['user_name']);
            $this->users->cur->user_birthday = htmlspecialchars($_POST['user_birthday']);
            $this->users->cur->user_city = htmlspecialchars($_POST['user_city']);
            $this->users->cur->user_phone = htmlspecialchars($_POST['user_phone']);
            $this->users->cur->user_about = htmlspecialchars($_POST['user_about']);
            $this->users->cur->user_vk_href = htmlspecialchars($_POST['user_vk_href']);
            $this->users->cur->user_ok_href = htmlspecialchars($_POST['user_ok_href']);
            if (!empty($_FILES['user_photo']['tmp_name'])) {
                $this->users->cur->user_photo = $this->Files->upload($_FILES['user_photo']);
            }
            if (!empty($_POST['user_pass'][0]) && !empty($_POST['user_pass'][1])) {
                if ($_POST['user_pass'][0] == $_POST['user_pass'][1]) {
                    $this->users->cur->user_pass = $this->users->hashpass($_POST['user_pass'][0]);
                    $changepass = true;
                    $this->msg->add('Пароль был изменен', 'success');
                } else {
                    $this->msg->add('Пароли не совпали', 'danger');
                }
            }
            $this->users->cur->save();
            if ($changepass) {
                $this->users->autorization($this->users->cur->user_mail, $this->users->cur->user_pass);
            }
            Tools::redirect('/users/profile', 'Информация была изменена!', 'success');
        }

        $this->view->page();
    }

    function loginAction()
    {
        $this->view->page();
    }

    function registrationAction()
    {
        $this->view->set_title('Регистрация');
        if (App::$cur->Users->curUser->user_id) {
            Tools::redirect('/', 'Вы уже зарегистрированы');
        }
        if (!empty($_POST)) {
            $error = false;
            $response = $this->Recaptcha->check($_POST['g-recaptcha-response']);
            if ($response) {
                if (!$response->success) {
                    $this->msg->add('Вы не прошли проверку на робота', 'danger');
                    $error = true;
                }
            } else {
                $this->msg->add('Произошла ошибка, попробуйте ещё раз');
                $error = true;
            }
            if (!$error) {
                $user_id = $this->Users->registration($_POST);
                if ($user_id) {
                    Tools::redirect('/');
                }
            }
        }
        $this->view->page();
    }

    function logoutAction()
    {
        setcookie("user_login", '', 0, "/");
        setcookie("user_mail", '', 0, "/");
        setcookie("user_pass", '', 0, "/");
        $accesses = $this->Config->module('Access');
        Tools::redirect($accesses['site']['denied_redirect'], 'Вы вышли из профиля');
    }

}

?>
