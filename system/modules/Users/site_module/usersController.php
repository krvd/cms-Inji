<?php

class usersController extends Controller
{

    function indexAction()
    {
        $this->url->redirect('/users/profile');
    }

    function structAction($action = '', $id = '')
    {
        $this->view->set_title('Структура');
        if (!empty($_POST['user_invite'])) {
            $user =Users\User::get($_POST['user_invite'], 'user_mail');
            if ($user) {
                $this->url->redirect('/users/struct', 'Пользователь с e-mail:' . $_POST['user_invite'] . ' уже зарегистрирован', 'danger');
            }
            $invite = UserInvite::get($_POST['user_invite'], 'ui_mail');
            if ($invite) {
                $this->url->redirect('/users/struct', 'Пользователь с e-mail:' . $_POST['user_invite'] . ' уже получал приглашение', 'danger');
            }
            $invite = new UserInvite();
            $invite->ui_user_id = $this->users->cur->user_id;
            $invite->ui_mail = trim($_POST['user_invite']);
            $invite->ui_code = $this->tools->randomString();
            while (UserInvite::get($invite->ui_code, 'ui_code')) {
                $invite->ui_code = $this->tools->randomString();
            }
            $invite->save();
            $this->_MAIL->send($this->users->cur->user_mail, $_POST['user_invite'], 'Доставка продуктов на дом в красноярске!', 'Для знакомства с сервисом пройдите по <a href = "http://' . INJI_DOMAIN_NAME . '/users/acceptInvite/' . $invite->ui_code . '">этой ссылке</a>');
            $this->url->redirect('/users/struct', 'Пользователь с e-mail:' . $_POST['user_invite'] . ' получил ваше приглашение', 'success');
        }
        $ii = 8;
        $levels = [];
        $userIds = $this->users->cur->user_id;
        $allUserIds = [];
        $allUserIds[] = $this->users->cur->user_id;
        $count = 0;
        for ($i = 1; $i <= $ii; $i++) {
            $this->db->where('user_parent_id', $userIds, 'IN');
            $this->db->join('catalog_marketing_user_cards', 'user_id = cmuc_user_id and cmuc_status = 1');
            $levels[$i] = $this->db->result_array($this->db->select('users'), 'user_id');
            $count += count($levels[$i]);
            $userIds = implode(',', array_keys($levels[$i]));
            if (!$userIds)
                break;
            $allUserIds = array_merge($allUserIds, array_keys($levels[$i]));
        }
        if ($action == 'getInfo' && $id) {
            $user =Users\User::get((int) $id);
            if (!$user || !in_array($user->user_id, $allUserIds)) {
                exit();
            }
            $return = [];
            $return['user_name'] = $user->user_name;
            $return['user_phone'] = $user->user_phone;
            $return['user_mail'] = $user->user_mail;
            $return['user_city'] = $user->user_city;
            if ($user->user_photo) {
                $file = $this->files->get($user->user_photo);
                $return['photo'] = $file['file_path'];
            } else {
                $return['photo'] = '/static/images/no-image.png';
            }


            $this->db->cols = 'SUM(cc_bonus_used)as `sum`';
            $this->db->where('cc_user_id', $user->user_id);
            $this->db->where('cc_status', '3,5', 'IN');
            $sum = $this->db->select('catalog_carts')->fetch_assoc();
            $return['used'] = $sum['sum'];


            $this->db->where('cub_user_id', $user->user_id);
            $this->db->where('cub_proof', 1);
            $this->db->group('cub_curency');
            $this->db->cols = '`cub_curency`, SUM(cub_sum)as `count`';
            $cubs = $this->db->result_array($this->db->select('catalog_user_bonuses'), 'cub_curency');
            if (!empty($cubs['ВР']['count']))
                $return['BP'] = (float) ($cubs['ВР']['count'] - $sum['sum']);
            else
                $return['BP'] = 0;

            if (!empty($cubs['УЕ']['count']))
                $return['YE'] = $cubs['УЕ']['count'];
            else
                $return['YE'] = 0;
            echo json_encode($return);
            exit();
        }
        $this->view->page(compact('levels', 'count'));
    }

    function acceptInviteAction($code)
    {
        $this->view->set_title('Приглашение');
        if (!$code) {
            $this->url->redirect('/');
        }
        $invite = UserInvite::get($code, 'ui_code');
        if (!$invite) {
            $this->url->redirect('/', 'Данный код недействителен', 'danger');
        }
        $user =Users\User::get($invite->ui_mail, 'user_mail');
        if ($user) {
            $this->url->redirect('/', 'Вы уже зарегистрированы', 'danger');
        }
        if (!empty($_POST['user_name'])) {
            $user_id = $this->Users->registration(array('user_name' => $_POST['user_name'], 'user_mail' => $invite->ui_mail, 'user_parent_id' => $invite->ui_user_id));
            if ($user_id) {
                $invite->ui_status = 1;
                $invite->save();
                $this->url->redirect('/');
            }
        }

        $this->view->page();
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
            $this->url->redirect('/users/profile', 'Информация была изменена!', 'success');
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
        if (Inji::app()->Users->cur->user_id) {
            $this->url->redirect('/', 'Вы уже зарегистрированы');
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
                    $this->url->redirect('/');
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
        $this->url->redirect($accesses['site']['denied_redirect'], 'Вы вышли из профиля');
    }

}

?>
