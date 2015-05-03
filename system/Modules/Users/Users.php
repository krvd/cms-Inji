<?php

class Users extends Module {

    function init() {
        $this->cur = new Users\User(array('user_group_id' => 1, 'user_role_id' => 1));
        if (isset($_GET['partner'])) {
            setcookie("partnerId", $_GET['partner'], time() + 360000, "/");
            $_COOKIE['partnerId'] = $_GET['partner'];
        }
        $cardCode = (int) substr(INJI_DOMAIN_NAME, 0, strpos(INJI_DOMAIN_NAME, '.'));
        if ($cardCode) {
            $this->db->where('cmuc_code', $cardCode);
            $this->db->where('cmuc_status', 1);
            $card = $this->db->select('catalog_marketing_user_cards')->fetch_assoc();
            if ($card) {
                setcookie("card", $cardCode, time() + 360000, "/", substr(INJI_DOMAIN_NAME, strpos(INJI_DOMAIN_NAME, '.') + 1));
            }
            $this->url->redirect('http://' . substr(INJI_DOMAIN_NAME, strpos(INJI_DOMAIN_NAME, '.') + 1));
        }
        if (Inji::app()->db->connect) {
            if (isset($_GET['logout']) && ($_COOKIE['user_login'] || $_COOKIE['user_mail'] ) && $_COOKIE['user_pass']) {
                setcookie("user_login", '', 0, "/");
                setcookie("user_mail", '', 0, "/");
                setcookie("user_pass", '', 0, "/");
                $accesses = Inji::app()->Config->module('Access');
                if (!empty($this->Access->modConf[Inji::app()->app['type']]['denied_redirect'])) {
                    $url = $this->Access->modConf[Inji::app()->app['type']]['denied_redirect'];
                } else {
                    $url = '/';
                }
                $this->url->redirect($url, 'Вы вышли из своего профиля', 'success');
            } elseif (!empty($_POST['autorization']) && (!empty($_POST['user_login']) || !empty($_POST['user_mail']) ) && !empty($_POST['user_pass'])) {
                if (!empty($_POST['user_login']))
                    if (strpos($_POST['user_login'], '@'))
                        $this->autorization($_POST['user_login'], $this->hashpass($_POST['user_pass']), 'mail');
                    else
                        $this->autorization($_POST['user_login'], $this->hashpass($_POST['user_pass']));
                elseif (strpos($_POST['user_mail'], '@'))
                    $this->autorization($_POST['user_mail'], $this->hashpass($_POST['user_pass']), 'mail');
                else
                    $this->autorization($_POST['user_mail'], $this->hashpass($_POST['user_pass']));
            }
            elseif (!empty($_POST['registration']) && (!empty($_POST['user_name']) && !empty($_POST['user_mail']) )) {
                $this->registration(trim(htmlspecialchars(strip_tags($_POST['user_name']))), $_POST['user_mail'], (!empty($_POST['user_login'])) ? trim(htmlspecialchars(strip_tags($_POST['user_login']))) : null);
            } elseif (!empty($_GET['passre']) && !empty($_GET['user_mail'])) {
                $this->passre($_GET['user_mail']);
            } elseif (!empty($_GET['passrecont']) && !empty($_GET['hash'])) {
                $this->passrecont($_GET['hash']);
            } elseif ((!empty($_COOKIE['user_login']) || !empty($_COOKIE['user_mail']) ) && !empty($_COOKIE['user_pass'])) {

                if (!empty($_COOKIE['user_login']))
                    $this->autorization($_COOKIE['user_login'], $_COOKIE['user_pass']);
                else
                    $this->autorization($_COOKIE['user_mail'], $_COOKIE['user_pass'], 'mail');
            }
        }
    }

    function passre($user_mail) {
        $user = $this->get($user_mail, 'mail');
        if (!$user) {
            $this->msg->add('Пользователь ' . $user_mail . ' не найден, проверьте првильность ввода e-mail или зарегистрируйтесь', 'danger');
            return false;
        }
        $this->db->where('up_user_id', $user->user_id);
        $this->db->where('up_status', 1);
        $up = $this->db->select('user_passre')->fetch_assoc();
        if ($up) {
            $this->db->where('up_id', $up['up_id']);
            $this->db->update('user_passre', array('up_status' => 2));
        }
        $hash = $this->hashpass(time() . $user->user_id);
        $this->db->insert('user_passre', array('up_user_id' => $user->user_id, 'up_status' => 1, 'up_hash' => $hash));
        $this->_MAIL->send('noreply@' . INJI_DOMAIN_NAME, $user_mail, 'Восстановление пароля на сайте ' . INJI_DOMAIN_NAME, 'Было запрошено восстановление пароля на сайте ' . INJI_DOMAIN_NAME . '<br />для продолжения восстановления пароля перейдите по ссылке: <a href = "http://' . INJI_DOMAIN_NAME . '/?passrecont=1&hash=' . $hash . '">' . INJI_DOMAIN_NAME . '/?passrecont=1&hash=' . $hash . '</a>');
        $this->url->redirect('/', 'На указанный почтовый ящик была выслана инструкция по восстановлению пароля', 'success');
    }

    function passrecont($hash) {
        $this->db->where('up_hash', $hash);
        $this->db->where('up_status', 1);
        $up = $this->db->select('user_passre')->fetch_assoc();
        if ($up) {
            $this->db->where('up_id', $up['up_id']);
            $this->db->update('user_passre', array('up_status' => 3));
            $pass = $this->genpass();
            $user = $this->get($up['up_user_id']);
            $user->user_pass = $this->hashpass($pass);
            $user->save();
            $this->users->autorization($user->user_mail, $user->user_pass, 'mail');
            $this->_MAIL->send('noreply@' . INJI_DOMAIN_NAME, $user->user_mail, 'Новый пароль на сайте ' . INJI_DOMAIN_NAME, 'Было запрошено восстановление пароля на сайте ' . INJI_DOMAIN_NAME . '<br />Ваш новый пароль: ' . $pass);
            $this->url->redirect('/', 'На указанный почтовый ящик был выслан новый пароль', 'success');
        }
    }

    function autorization($login, $pass, $ltype = 'login') {
        $user = $this->get($login, $ltype);
        if ($user && $user->user_pass === $pass) {
            $this->cur = $user;
            if (!headers_sent()) {
                setcookie("user_login", $user->user_login, time() + 360000, "/");
                setcookie("user_mail", $user->user_mail, time() + 360000, "/");
                setcookie("user_pass", $user->user_pass, time() + 360000, "/");
            }
            $user->user_last_activ = 'CURRENT_TIMESTAMP';
            $user->save();
            if (!empty($_POST['autorization']) && !empty($this->Access->modConf[Inji::app()->app['type']]['login_redirect'])) {
                $this->url->redirect($this->Access->modConf[Inji::app()->app['type']]['login_redirect']);
            }
            return true;
        } elseif (!empty($_POST['autorization'])) {
            if ($user) {
                Inji::app()->msg->add('Вы ошиблись при наборе пароля или логина, попробуйте ещё раз или воспользуйтесь <a href = "?passre=1&user_mail=' . $user->user_mail . '">Восстановлением пароля</a>', 'danger');
            } else {
                Inji::app()->msg->add('Данный почтовый ящик не зарегистрирован в системе', 'danger');
            }
            return false;
        } else
            return false;
    }

    function getDiffLevel($fromId, $toId) {
        $i = 1;
        if ($fromId == $toId)
            return 0;
        while ($from =Users\User::get($fromId)) {
            if ($from->user_parent_id == $toId)
                return $i;
            $fromId = $from->user_parent_id;
            $i++;
            if (!$from->user_parent_id)
                return 0;
        }
    }

    function get($idn = false, $ltype = 'id') {
        if (!$idn)
            return false;

        if (is_numeric($idn) && !$ltype != 'login')
            $user =Users\User::get($idn, 'user_id');
        elseif ($ltype == 'login')
            $user =Users\User::get($idn, 'user_login');
        else
            $user =Users\User::get($idn, 'user_mail');

        if (!$user)
            return array();

        return $user;
    }

    function get_list($page = 1, $limit = 20, $activ = true, $count = false, $ids = '') {
        $page = intval($page);
        $limit = intval($limit);

        if (!$page || $page < 0)
            $page = 1;
        if (!$limit)
            $limit = 20;

        $start = $page * $limit - $limit;
        $this->db->limit($start, $limit);
        if ($activ === true)
            $this->db->where('user_activ', '');
        elseif ($active === false)
            $this->db->where('user_activ', '', '!=');

        if ($ids)
            $this->db->where('user_id', $ids, 'IN');

        if ($count) {
            $this->db->cols = 'count(`user_id`) as `count`';
            $count = $this->db->select('users')->fetch_assoc();
            return $count['count'];
        } else
            $users = $this->db->result_array($this->db->select('users'), 'user_id');

        if (!$users)
            return array();
        foreach ($users as $user_id => $user) {
            if (!empty($user['user_first_name']) || !empty($user['user_last_name']))
                $users[$user_id]['show_name'] = "{$user['user_first_name']} {$user['user_last_name']}";
            elseif (!empty($user['user_login']))
                $users[$user_id]['show_name'] = $user['user_login'];
            else
                $users[$user_id]['show_name'] = $user['user_mail'];

            if (!empty($user['user_photo']))
                $users[$user_id]['show_photo'] = "/static/img/users/avatars/{$user['user_photo']}";
            elseif (!empty($user['user_sex']) && $user['user_sex'] != 2)
                $users[$user_id]['show_photo'] = "/templates/index/img/avatar-m.png";
            else
                $users[$user_id]['show_photo'] = "/templates/index/img/avatar-w.png";
        }
        return $users;
    }

    function registration($data) {
        extract($data);
        if (empty($user_name)) {
            $this->msg->add('Вы не ввели ФИО', 'danger');
            return false;
        }

        if (empty($user_mail)) {
            $this->msg->add('Вы не ввели E-mail', 'danger');
            return false;
        }
        if (!filter_var($user_mail, FILTER_VALIDATE_EMAIL)) {
            $this->msg->add('Вы ввели не корректный E-mail', 'danger');
            return false;
        }

        if (!empty($this->users->modConf['sponsors']) && !empty($user_parent_id)) {
            $user =Users\User::get((int) $user_parent_id);
            if (!$user) {
                $this->msg->add('Спонсор с данныйм id не найден', 'danger');
                return false;
            }
        } elseif (!empty($_COOKIE['partnerId'])) {
            $user =Users\User::get((int) $_COOKIE['partnerId']);
            if (!$user) {
                $user_parent_id = 1;
            } else {
                $user_parent_id = $user->user_id;
            }
        } elseif (!empty($cardCode)) {
            $this->db->where('cmuc_code', (int) $cardCode);
            $this->db->where('cmuc_status', 1);
            $card = $this->db->select('catalog_marketing_user_cards')->fetch_assoc();
            if ($card) {
                $user_parent_id = $card['cmuc_user_id'];
            } else {
                $this->msg->add('Карты с таким номером не найдено', 'danger');
                return false;
            }
        } elseif (!empty($_COOKIE['card'])) {
            $this->db->where('cmuc_code', (int) $_COOKIE['card']);
            $this->db->where('cmuc_status', 1);
            $card = $this->db->select('catalog_marketing_user_cards')->fetch_assoc();
            if ($card) {
                $user_parent_id = $card['cmuc_user_id'];
            }
        } else {
            $user_parent_id = 1;
        }





        $user = $this->get($user_mail, 'mail');
        if ($user) {
            $this->msg->add('Введенный вами почтовый ящик зарегистрирован в нашей системе, войдите или введите другой почтовый ящик', 'danger');
            return false;
        }
        if (empty($user_login)) {
            $user_login = $user_mail;
        }
        $user = $this->get($user_login, 'login');
        if ($user) {
            $this->msg->add('Введенный вами логин зарегистрирован в нашей системе, войдите или введите другой логин', 'danger');
            return false;
        }
        if (empty($user_city)) {
            $user_city = '';
        }
        if (empty($user_birthday)) {
            $user_birthday = '';
        }
        if (empty($user_phone)) {
            $user_phone = '';
        }


        $pass = $this->genpass();
        $user_id = $this->new_user(array(
            'user_pass' => $this->hashpass($pass),
            'user_mail' => $user_mail,
            'user_parent_id' => $user_parent_id,
            'user_login' => htmlspecialchars($user_login),
            'user_name' => htmlspecialchars($user_name),
            'user_city' => htmlspecialchars($user_city),
            'user_birthday' => htmlspecialchars($user_birthday),
            'user_phone' => htmlspecialchars($user_phone),
            'user_role_id' => 2,
            'user_group_id' => 2
        ));
        if (!$user_id) {
            $this->msg->add('Не удалось зарегистрировать', 'danger');
            return false;
        }
        if (!empty($_COOKIE['partnerId'])) {
            setcookie("partnerId", '', 0, "/");
        }
        if (!empty($_COOKIE['card'])) {
            setcookie("card", '', 0, "/", '.' . INJI_DOMAIN_NAME);
        }
        $this->autorization($user_mail, $this->hashpass($pass), 'mail');

        $from = 'noreply@' . INJI_DOMAIN_NAME;
        $to = $user_mail;
        $theme = 'Регистрация на сайте ' . INJI_DOMAIN_NAME;
        $text = 'Вы были зарегистрированы на сайте ' . INJI_DOMAIN_NAME . '<br />для входа используйте ваш почтовый ящик в качестве логина и пароль: ' . $pass;
        $this->_MAIL->send($from, $to, $theme, $text);
        $this->msg->add('Вы были зарегистрированы. На указанный почтовый ящик был выслан ваш пароль', 'success');

        return $user_id;
    }

    function update($user_id = 0, $data = array()) {
        Inji::app()->db->where('user_id', $user_id);
        Inji::app()->db->update('users', $data);
    }

    function new_user($data) {
        return $this->db->insert('users', $data);
    }

    function delete_user($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->delete('users');
    }

    function genpass() {
        // Символы, которые будут использоваться в пароле.

        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";

        // Количество символов в пароле.

        $max = 10;

        // Определяем количество символов в $chars

        $size = StrLen($chars) - 1;

        // Определяем пустую переменную, в которую и будем записывать символы.

        $password = '';

        // Создаём пароль.

        while ($max--)
            $password.=$chars[rand(0, $size)];

        return $password;
    }

    function hashpass($pass) {
        return hash('sha256', 'asd908436#*U&89' . hash('sha256', $pass . 'asdo409dv,bmdf') . ')#(OKOMVIROI#)#_(');
    }

}

?>
