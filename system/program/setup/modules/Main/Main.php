<?php

/**
 * Main module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Main extends Module
{
    function init()
    {
        $config = Config::share();
        if (empty(App::$cur->params[0]) || App::$cur->params[0] != 'enter') {
            if (empty($_COOKIE['systemPass']) || empty($config['systemPass'])) {
                Tools::redirect('/setup/enter', 'Введите системный пароль');
            }
            if (!empty($_COOKIE['systemPass']) && !empty($config['systemPass']) && $_COOKIE['systemPass'] != $config['systemPass']) {
                Tools::redirect('/setup/enter', 'Не верный системный пароль');
            }
        }
    }

}
