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
class ViewController extends Controller {

    function editorcssAction() {
        ///view/current/css/editor.css
        if (file_exists($this->view->templatesPath . '/' . $this->view->template['name'] . '/css/editor.css')) {
            Tools::redirect('/static/templates/' . $this->view->template['name'] . '/css/editor.css');
        }
        else {
            header("Content-type: text/css");
            exit();
        }
    }

}
