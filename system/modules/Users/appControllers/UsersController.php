<?php

class UsersController extends Controller
{
    function indexAction()
    {
        Tools::redirect('/users/cabinet/profile');
    }

    function cabinetAction($activeSection = '')
    {
        $sections = $this->module->getSnippets('cabinetSection');
        $extends = Module::getExtensions('Users', 'snippets', 'cabinetSection');
        $sections = array_merge($sections, $extends);
        if (!empty($sections[$activeSection]['name'])) {
            $this->view->setTitle($sections[$activeSection]['name'] . ' - Личный кабинет');
        } else {
            $this->view->setTitle('Личный кабинет');
        }
        $this->view->page(['data' => compact('widgets', 'sections', 'activeSection')]);
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
