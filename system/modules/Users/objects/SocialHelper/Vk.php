<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Users\SocialHelper;

class Vk extends \Users\SocialHelper
{
    static function auth()
    {
        $config = static::getConfig();
        if (empty($_GET['code']) && empty($_GET['error'])) {
            $query = [
                'client_id' => $config['appId'],
                'client_secret' => $config['secret'],
                'scope' => 'email',
                'response_type' => 'code',
                'display' => 'page',
                'redirect_uri' => 'http://' . INJI_DOMAIN_NAME . '/users/social/auth/vk'
            ];
            \Tools::redirect("https://oauth.vk.com/authorize?" . http_build_query($query));
        }
        if (empty($_GET['code']) && !empty($_GET['error'])) {
            \Tools::redirect('/', 'Произошла ошибка во время авторизации через соц. сеть: ' . $_GET['error_description']);
        }
        $query = [
            'client_id' => $config['appId'],
            'client_secret' => $config['secret'],
            'code' => $_GET['code'],
            'redirect_uri' => 'http://' . INJI_DOMAIN_NAME . '/users/social/auth/vk'
        ];
        $result = @file_get_contents("https://oauth.vk.com/access_token?" . http_build_query($query));
        if ($result === false) {
            \Tools::redirect('/', 'Во время авторизации произошли ошибки', 'danger');
        }
        $result = json_decode($result, true);
        if (empty($result['user_id'])) {
            \Tools::redirect('/', 'Во время авторизации произошли ошибки', 'danger');
        }
        $userQuery = [
            'user_id' => $result['user_id'],
            'fields' => 'sex, bdate, photo_max_orig, home_town',
            'access_token' => $result['access_token']
        ];
        $userResult = @file_get_contents("https://api.vk.com/method/users.get?" . http_build_query($userQuery));
        if (!$userResult) {
            \Tools::redirect('/', 'Во время авторизации произошли ошибки', 'danger');
        }
        $userDetail = json_decode($userResult, true);
        if (empty($userDetail['response'][0])) {
            \Tools::redirect('/', 'Во время авторизации произошли ошибки', 'danger');
        }
        $social = static::getObject();
        $userSocial = \Users\User\Social::get([['uid', $result['user_id']], ['social_id', $social->id]]);
        if ($userSocial && $userSocial->user) {
            \App::$cur->users->newSession($userSocial->user);
            \Tools::redirect('/');
        } else {
            if ($userSocial && !$userSocial->user) {
                $userSocial->delete();
            }
            if (!\Users\User::$cur->id) {
                $user = new \Users\User();
                if (!empty($result['email'])) {
                    $mailFind = \Users\User::get($result['email'], 'mail');
                    if ($mailFind) {
                        \Tools::redirect('/', 'E-mail ' . $result['email'] . ' уже зарегистрирован в системе, но к данному аккаунту нет привязки вашей страницы в соц сети. Вход через соц сеть невозможен', 'danger');
                    }
                    $user->login = $user->mail = $result['email'];
                }
                $user->group_id = 2;
                $user->role_id = 2;
                $user->save();
                $userInfo = new \Users\Info();
                $userInfo->user_id = $user->id;
                $userInfo->agency_id = 59;
                if (!empty($userDetail['response'][0]['photo_max_orig'])) {
                    $userInfo->photo_file_id = \App::$cur->files->uploadFromUrl($userDetail['response'][0]['photo_max_orig']);
                }
                $userInfo->first_name = $userDetail['response'][0]['first_name'];
                $userInfo->last_name = $userDetail['response'][0]['last_name'];
                $userInfo->city = $userDetail['response'][0]['home_town'];
                $userInfo->sex = $userDetail['response'][0]['sex'] == 2 ? 1 : ($userDetail['response'][0]['sex'] == 2 ? 1 : 0);
                $userInfo->bday = substr_count($userDetail['response'][0]['bdate'], '.') == 2 ? \DateTime::createFromFormat('d.m.Y', $userDetail['response'][0]['bdate'])->format('Y-m-d') : (substr_count($userDetail['response'][0]['bdate'], '.') == 1 ? \DateTime::createFromFormat('d.m', $userDetail['response'][0]['bdate'])->format('Y-m-1') : '0000-00-00');
                $userInfo->save();
            } else {
                $user = \Users\User::$cur;
            }
            $userSocial = new \Users\User\Social();
            $userSocial->uid = $result['user_id'];
            $userSocial->social_id = $social->id;
            $userSocial->user_id = $user->id;
            $userSocial->save();
            \App::$cur->users->newSession($user);
            \Tools::redirect('/users/cabinet/profile', 'Вы успешно зарегистрировались через ВКонтакте', 'success');
        }
    }

}
