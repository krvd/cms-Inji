<?php

class Users extends Module {
    function init() {
        \Users\User::$cur = new Users\User(array('group_id' => 1, 'role_id' => 1));
        
        if (!App::$cur->db->connect) {
            return;
        }

        if (isset($_GET['logout'])) {
            return $this->logOut();
        }
        if (isset($_POST['autorization']) && filter_input(INPUT_POST, 'user_login') && filter_input(INPUT_POST, 'user_pass')) {
            return $this->autorization(filter_input(INPUT_POST, 'user_login'), filter_input(INPUT_POST, 'user_pass'), strpos(filter_input(INPUT_POST, 'user_login'), '@') ? 'mail' : 'login');
        }
        if (isset($_GET['passre']) && filter_input(INPUT_GET, 'user_mail')) {
            return $this->passre(filter_input(INPUT_GET, 'user_mail'));
        }
        if (!empty($_GET['passrecont']) && filter_input(INPUT_GET, 'hash')) {
            $this->passrecont(filter_input(INPUT_GET, 'hash'));
        }
        if (filter_input(INPUT_COOKIE, 'user_session_hash') && filter_input(INPUT_COOKIE, 'user_id')) {
            return $this->cuntinueSession(filter_input(INPUT_COOKIE, 'user_session_hash'), filter_input(INPUT_COOKIE, 'user_id'));
        }
    }

    function logOut() {
        setcookie("user_session_hash", '', 0, "/");
        setcookie("user_id", '', 0, "/");
        $accesses = Config::module('Access');
        if (!empty($this->Access->modConf[App::$cur->app->type]['denied_redirect'])) {
            $url = $this->Access->modConf[App::$cur->app->type]['denied_redirect'];
        } else {
            $url = '/';
        }
        Tools::redirect($url, 'Вы вышли из своего профиля', 'success');
    }

    function cuntinueSession($hash, $userId) {
        $session = Users\Session::get([
                    ['user_id', $userId],
                    ['agent', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')],
                    ['ip', filter_input(INPUT_SERVER, 'REMOTE_ADDR')],
                    ['hash', $hash]
        ]);
        if ($session) {
            Users\User::$cur = Users\User::get($session->user_id);
            Users\User::$cur->last_activ = 'CURRENT_TIMESTAMP';
            Users\User::$cur->save();
        } else {
            setcookie("user_session_hash", '', 0, "/");
            setcookie("user_id", '', 0, "/");
            Msg::add('Ваша сессия устарела или более недействительна, вам необходимо пройти <a href = "/users/login">авторазиацию</a> заного', 'info');
        }
    }

    function passre($user_mail) {
        $user = $this->get($user_mail, 'mail');
        if (!$user) {
            Msg::add('Пользователь ' . $user_mail . ' не найден, проверьте првильность ввода e-mail или зарегистрируйтесь', 'danger');
            return false;
        }
        $passre = Users\Passre::get([['user_id', $user->id], ['status', 1]]);
        if ($passre) {
            $passre->status = 2;
            $passre->save();
        }
        $hash = $user->id . '_' . Tools::randomString(255);
        $passre = new Users\Passre(['user_id' => $user->id, 'status' => 1, 'hash' => $hash]);
        Tools::sendMail('noreply@' . INJI_DOMAIN_NAME, $user_mail, 'Восстановление пароля на сайте ' . INJI_DOMAIN_NAME, 'Было запрошено восстановление пароля на сайте ' . INJI_DOMAIN_NAME . '<br />для продолжения восстановления пароля перейдите по ссылке: <a href = "http://' . INJI_DOMAIN_NAME . '/?passrecont=1&hash=' . $hash . '">' . INJI_DOMAIN_NAME . '/?passrecont=1&hash=' . $hash . '</a>');
        Tools::redirect('/', 'На указанный почтовый ящик была выслана инструкция по восстановлению пароля', 'success');
    }

    function passrecont($hash) {
        $passre = Users\Passre::get([[$hash, 'hash'], ['status', 1]]);
        if ($passre) {
            $passre->status = 3;
            $pass = Tools::randomString(10);
            $user = Users\User::get($passre->user_id);
            $user->pass = $this->hashpass($pass);
            $user->save();
            $this->users->autorization($user->mail, $user->pass, 'mail');
            Tools::sendMail('noreply@' . INJI_DOMAIN_NAME, $user->mail, 'Новый пароль на сайте ' . INJI_DOMAIN_NAME, 'Было запрошено восстановление пароля на сайте ' . INJI_DOMAIN_NAME . '<br />Ваш новый пароль: ' . $pass);
            Tools::redirect('/', 'На указанный почтовый ящик был выслан новый пароль', 'success');
        }
    }

    function autorization($login, $pass, $ltype = 'login') {
        $user = $this->get($login, $ltype);
        if ($user && $this->verifypass($pass, $user->pass)) {
            $hash = Tools::randomString(255);
            $session = Users\Session::get([
                        ['user_id', $user->id],
                        ['agent', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')],
                        ['ip', filter_input(INPUT_SERVER, 'REMOTE_ADDR')]
            ]);
            if (!$session) {
                $session = new Users\Session([
                    'user_id' => $user->id,
                    'agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
                    'ip' => filter_input(INPUT_SERVER, 'REMOTE_ADDR')
                ]);
            }
            $session->hash = $hash;
            $session->save();
            if (!headers_sent()) {
                setcookie("user_session_hash", $session->hash, time() + 360000, "/");
                setcookie("user_id", $session->user_id, time() + 360000, "/");
            }

            Users\User::$cur = $user;
            Users\User::$cur->last_activ = 'CURRENT_TIMESTAMP';
            Users\User::$cur->save();

            if (isset($_POST['autorization']) && !empty(App::$cur->Access->config[App::$cur->app->type]['login_redirect'])) {
                Tools::redirect(App::$cur->Access->config[App::$cur->app->type]['login_redirect']);
            }
            return true;
        }
        if (isset($_POST['autorization'])) {
            if ($user) {
                Msg::add('Вы ошиблись при наборе пароля или логина, попробуйте ещё раз или воспользуйтесь <a href = "?passre=1&user_mail=' . $user->mail . '">Восстановлением пароля</a>', 'danger');
            } else {
                Msg::add('Данный почтовый ящик не зарегистрирован в системе', 'danger');
            }
        }

        return false;
    }

    function get($idn = false, $ltype = 'id') {
        if (!$idn)
            return false;

        if (is_numeric($idn) && !$ltype != 'login')
            $user = Users\User::get($idn, 'id');
        elseif ($ltype == 'login')
            $user = Users\User::get($idn, 'login');
        else
            $user = Users\User::get($idn, 'mail');
        if (!$user)
            return array();

        return $user;
    }

    function registration($data) {
        extract($data);
        if (empty($user_name)) {
            Msg::add('Вы не ввели ФИО', 'danger');
            return false;
        }

        if (empty($user_mail)) {
            Msg::add('Вы не ввели E-mail', 'danger');
            return false;
        }
        if (!filter_var($user_mail, FILTER_VALIDATE_EMAIL)) {
            Msg::add('Вы ввели не корректный E-mail', 'danger');
            return false;
        }

        $user = $this->get($user_mail, 'mail');
        if ($user) {
            Msg::add('Введенный вами почтовый ящик зарегистрирован в нашей системе, войдите или введите другой почтовый ящик', 'danger');
            return false;
        }
        if (empty($user_login)) {
            $user_login = $user_mail;
        }
        $user = $this->get($user_login, 'login');
        if ($user) {
            Msg::add('Введенный вами логин зарегистрирован в нашей системе, войдите или введите другой логин', 'danger');
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


        $pass = Tools::randomString(10);
        $user = new Users\User([
            'pass' => $this->hashpass($pass),
            'mail' => $user_mail,
            'login' => htmlspecialchars($user_login),
            'name' => htmlspecialchars($user_name),
            'city' => htmlspecialchars($user_city),
            'birthday' => htmlspecialchars($user_birthday),
            'phone' => htmlspecialchars($user_phone),
            'role_id' => 2,
            'group_id' => 2
        ]);
        $user->save();
        if (!$user->id) {
            Msg::add('Не удалось зарегистрировать', 'danger');
            return false;
        }
        $this->autorization($user_mail, $pass, 'mail');

        $from = 'noreply@' . INJI_DOMAIN_NAME;
        $to = $user_mail;
        $theme = 'Регистрация на сайте ' . INJI_DOMAIN_NAME;
        $text = 'Вы были зарегистрированы на сайте ' . INJI_DOMAIN_NAME . '<br />для входа используйте ваш почтовый ящик в качестве логина и пароль: ' . $pass;
        Tools::sendMail($from, $to, $theme, $text);
        Msg::add('Вы были зарегистрированы. На указанный почтовый ящик был выслан ваш пароль', 'success');

        return $user_id;
    }

    function hashpass($pass) {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    function verifypass($pass, $hash) {
        return password_verify($pass, $hash);
    }

}

?>
