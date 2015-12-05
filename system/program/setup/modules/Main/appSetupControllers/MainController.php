<?php

/**
 * setup controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MainController extends Controller
{
    function indexAction()
    {
        $this->view->setTitle('Система');
        $this->view->page();
    }

    function enterAction()
    {
        $config = Config::share();
        if (!empty($_POST['systemPass'])) {
            if (empty($config['systemPass'])) {
                $config['systemPass'] = $_POST['systemPass'];
                $config['installed'] = true;
                Config::save('share', $config);
            }
            if ($_POST['systemPass'] == $config['systemPass']) {
                setcookie('systemPass', $_POST['systemPass'], 0, '/setup');
            }

            Tools::redirect('/setup');
        }
        if (!empty($config['systemPass']) && !empty($_COOKIE['systemPass']) && $_COOKIE['systemPass'] == $config['systemPass']) {
            Tools::redirect('/setup');
        }
        $this->view->setTitle('Enter');
        $this->view->page();
    }

}
