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

/**
 * Description of SocialController
 *
 * @author inji
 */
class SocialController extends Controller
{
    function authAction($socialCode = '')
    {
        if (!$socialCode) {
            Tools::redirect('/', 'Не указана соц сеть');
        }
        $social = Users\Social::get($socialCode, 'code');
        if (!$social) {
            Tools::redirect('/', 'Такой соц. сети не найдено');
        }
        $helper = 'Users\SocialHelper\\' . $social->object_name;
        $helper::auth();
    }

    function disconnectAction($socialCode = '')
    {
        if (!$socialCode) {
            Tools::redirect('/', 'Не указана соц сеть');
        }
        $social = Users\Social::get($socialCode, 'code');
        if (!$social) {
            Tools::redirect('/', 'Такой соц. сети не найдено');
        }
        foreach (\Users\User::$cur->socials as $userSocial) {
            if ($userSocial->social_id == $social->id) {
                $userSocial->delete();
                Tools::redirect('/', 'Связь с соц. сетью ' . $social->name . ' была удалена');
            }
        }
        Tools::redirect('/', 'Связь с соц. сетью ' . $social->name . ' не найдена');
    }

}
