<?php

/**
 * Modules controller class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class ModulesController extends Controller {

    function indexAction($appType = 'app') {
        App::$cur->view->setTitle('Управление модулями');
        $systemModules = array_slice(scandir(INJI_SYSTEM_DIR . '/modules'), 2);
        App::$cur->view->page(['data' => compact('systemModules')]);
    }

}
