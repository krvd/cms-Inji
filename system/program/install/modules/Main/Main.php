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
        if (!empty($config['installed'])) {
            Tools::redirect('/', 'Приложение уже установлено');
        }
        Users\User::$cur->group_id = 3;
    }

}
