<?php

class UsersController extends Controller
{
    function indexAction()
    {
        Tools::redirect('/users/cabinet/profile');
    }

    function cabinetAction($activeSection = '')
    {
        $bread = [];

        $sections = $this->module->getSnippets('cabinetSection');
        if (!empty($sections[$activeSection]['name'])) {
            $this->view->setTitle($sections[$activeSection]['name'] . ' - Личный кабинет');
            $bread[] = ['text' => 'Личный кабинет', 'href' => '/users/cabinet'];
            $bread[] = ['text' => $sections[$activeSection]['name']];
        } else {
            $this->view->setTitle('Личный кабинет');
            $bread[] = ['text' => 'Личный кабинет'];
        }
        $this->view->page(['data' => compact('widgets', 'sections', 'activeSection', 'bread')]);
    }

    function loginAction()
    {
        $this->view->setTitle('Авторизация');
        $bread = [];
        $bread[] = ['text' => 'Авторизация'];
        $this->view->page(['data' => compact('bread')]);
    }

    function registrationAction()
    {
        $this->view->setTitle('Регистрация');
        if (Users\User::$cur->user_id) {
            Tools::redirect('/', 'Вы уже зарегистрированы');
        }
        if (!empty($_POST)) {
            $error = false;
            if ($this->Recaptcha) {
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
            }
            if (!$error) {
                if ($this->Users->registration($_POST)) {
                    Tools::redirect('/');
                }
            }
        }
        $this->view->setTitle('Регистрация');
        $bread = [];
        $bread[] = ['text' => 'Регистрация'];
        $this->view->page(['data' => compact('bread')]);
    }

    function activationAction($userId = 0, $hash = '')
    {
        $user = \Users\User::get((int) $userId);
        if (!$user || $user->activation !== (string) $hash) {
            Tools::redirect('/', 'Во время активации произошли ошибки', 'danger');
        }
        $user->activation = '';
        $user->save();
        Tools::redirect('/', 'Вы успешно активировали ваш аккаунт, теперь вы можете войти');
    }

}
